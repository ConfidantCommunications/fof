<?php
/**
 * @package	    FrameworkOnFramework.UnitTest
 * @subpackage  TableBehaviors
 *
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once 'accessDataprovider.php';

class F0FModelBehaviorAccessTest extends FtestCaseDatabase
{
    protected function setUp()
    {
        $loadDataset = true;
        $annotations = $this->getAnnotations();

        // Do I need a dataset for this set or not?
        if(isset($annotations['method']) && isset($annotations['method']['preventDataLoading']))
        {
            $loadDataset = false;
        }

        parent::setUp($loadDataset);

        F0FPlatform::forceInstance(null);
        F0FTable::forceInstance(null);
    }

    /**
     * @group               accessOnAfterBuildQuery
     * @group               F0FModelBehavior
     * @covers              F0FModelBehaviorAccess::onAfterBuildQuery
     * @dataProvider        getTestOnAfterBuildQuery
     */
    public function testOnAfterBuildQuery($modelinfo, $test, $checks)
    {
        $config['option'] = 'com_foftest';
        $config['name']   = $modelinfo['name'];
        $config['table']  = F0FInflector::singularize($modelinfo['name']);
        $config['input']  = array('option' => 'com_foftest', 'view' => $modelinfo['name']);

        $model = $this->getMock('F0FModel', array('applyAccessFiltering', 'getTable'), array($config));

        $platform = $this->getMock('F0FPlatform', array('isFrontend'));
        $platform->expects($this->any())->method('isFrontend')->will($this->returnValue($test['frontend']));

        F0FPlatform::forceInstance($platform);

        $reflection = new ReflectionProperty($model, 'modelDispatcher');
        $reflection->setAccessible(true);
        $dispatcher = $reflection->getValue($model);

        $behavior = new F0FModelBehaviorAccess($dispatcher);

        $table = F0FTable::getAnInstance(ucfirst(F0FInflector::singularize($modelinfo['name'])), 'FoftestTable');

        if(isset($test['aliases']))
        {
            foreach($test['aliases'] as $column => $alias)
            {
                $table->setColumnAlias($column, $alias);
            }
        }

        $model->expects($this->any())->method('getTable')->will($this->returnValue($table));

        if($checks['execute'])
        {
            $model->expects($this->once())->method('applyAccessFiltering')->with(null);
        }
        else
        {
            $model->expects($this->never())->method('applyAccessFiltering');
        }

        $null  = null;
        $behavior->onAfterBuildQuery($model, $null);
    }

    /**
     * @group               accessOnAfterGetItem
     * @group               F0FModelBehavior
     * @covers              F0FModelBehaviorAccess::onAfterGetItem
     * @dataProvider        getTestOnAfterGetItem
     */
    public function testOnAfterGetItem($modelinfo, $test, $checks)
    {
        $config = array();

        if(isset($test['aliases']['tbl_key']))
        {
            $config['tbl_key'] = $test['aliases']['tbl_key'];
        }

        $model = F0FModel::getTmpInstance($modelinfo['name'], 'FoftestModel');
        $table = F0FTable::getAnInstance(ucfirst(F0FInflector::singularize($modelinfo['name'])), 'FoftestTable', $config);

        if(isset($test['aliases']))
        {
            foreach($test['aliases'] as $column => $alias)
            {
                $table->setColumnAlias($column, $alias);
            }
        }

        $user = $this->getMock('JUser', array('getAuthorisedViewLevels'));
        $user->expects($this->any())->method('getAuthorisedViewLevels')->will($this->returnValue($test['views']));

        $platform = $this->getMock('F0FPlatform', array('getUser'));
        $platform->expects($this->any())->method('getUser')->will($this->returnValue($user));

        F0FPlatform::forceInstance($platform);

        $reflection = new ReflectionProperty($model, 'modelDispatcher');
        $reflection->setAccessible(true);
        $dispatcher = $reflection->getValue($model);

        $behavior = new F0FModelBehaviorAccess($dispatcher);

        $table->load($test['loadid']);

        $saved = clone $table;
        $behavior->onAfterGetItem($model, $table);

        if($checks['nullify'])
        {
            $this->assertNull($table, "F0FModelBehaviorAccess::onAfterGetItem should nullify the table record when the user can't access the record");
        }
        else
        {
            $this->assertEquals($saved, $table, 'F0FModelBehaviorAccess::onAfterGetItem should leave the record untouched if the user can access the record');
        }
    }

    public function getTestOnAfterBuildQuery()
    {
        return accessDataprovider::getTestOnAfterBuildQuery();
    }

    public function getTestOnAfterGetItem()
    {
        return accessDataprovider::getTestOnAfterGetItem();
    }
}
