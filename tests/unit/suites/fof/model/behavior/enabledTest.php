<?php
/**
 * @package	    FrameworkOnFramework.UnitTest
 * @subpackage  TableBehaviors
 *
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once 'enabledDataprovider.php';

class F0FModelBehaviorEnabledTest extends FtestCaseDatabase
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
     * @group               enabledOnAfterBuildQuery
     * @group               F0FModelBehavior
     * @covers              F0FModelBehaviorEnabled::onAfterBuildQuery
     * @dataProvider        getTestOnAfterBuildQuery
     */
    public function testOnAfterBuildQuery($modelinfo, $test, $checks)
    {
        $config['option'] = 'com_foftest';
        $config['name']   = $modelinfo['name'];
        $config['table']  = F0FInflector::singularize($modelinfo['name']);
        $config['input']  = array('option' => 'com_foftest', 'view' => $modelinfo['name']);

        $model = $this->getMock('F0FModel', array('getTable'), array($config));

        $platform = $this->getMock('F0FPlatform', array('isFrontend'));
        $platform->expects($this->any())->method('isFrontend')->will($this->returnValue($test['frontend']));

        F0FPlatform::forceInstance($platform);

        $reflection = new ReflectionProperty($model, 'modelDispatcher');
        $reflection->setAccessible(true);
        $dispatcher = $reflection->getValue($model);

        $behavior = new F0FModelBehaviorEnabled($dispatcher);

        $table = F0FTable::getAnInstance(ucfirst(F0FInflector::singularize($modelinfo['name'])), 'FoftestTable');

        if(isset($test['aliases']))
        {
            foreach($test['aliases'] as $column => $alias)
            {
                $table->setColumnAlias($column, $alias);
            }
        }

        $model->expects($this->any())->method('getTable')->will($this->returnValue($table));

        $behavior->onAfterBuildQuery($model, $test['query']);

        $this->assertEquals((string)$checks['query'], (string)$test['query'], 'F0FModelBehaviorEnabled::onAfterBuildQuery failed to modify the query object');
    }

    /**
     * @group               enabledOnAfterGetItem
     * @group               F0FModelBehavior
     * @covers              F0FModelBehaviorEnabled::onAfterGetItem
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

        $reflection = new ReflectionProperty($model, 'modelDispatcher');
        $reflection->setAccessible(true);
        $dispatcher = $reflection->getValue($model);

        $behavior = new F0FModelBehaviorEnabled($dispatcher);

        $table->load($test['loadid']);

        $saved = clone $table;
        $behavior->onAfterGetItem($model, $table);

        if($checks['nullify'])
        {
            $this->assertNull($table, "F0FModelBehaviorEnabled::onAfterGetItem should nullify the table record when it's not enabled");
        }
        else
        {
            $this->assertEquals($saved, $table, "F0FModelBehaviorEnabled::onAfterGetItem should leave the record untouched if it's enabled");
        }
    }

    public function getTestOnAfterBuildQuery()
    {
        return enabledDataprovider::getTestOnAfterBuildQuery();
    }

    public function getTestOnAfterGetItem()
    {
        return enabledDataprovider::getTestOnAfterGetItem();
    }
}
