<?php
/**
 * @package	    FrameworkOnFramework.UnitTest
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

use org\bovigo\vfs\vfsStream;
require_once 'modelDataprovider.php';
require_once JPATH_TESTS.'/unit/core/table/custom.php';

class F0FModelTest extends FtestCaseDatabase
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

		// TODO It seems that another test is "polluting" F0FPlatform instance, leaving a Mock Object as current instance
		F0FPlatform::forceInstance(null);
        F0FTable::forceInstance(null);
	}

    /**
     * @group               modelTestGetId
     * @group               F0FModel
     * @covers              F0FModel::getId
     * @preventDataLoading
     */
    public function testGetId()
    {
        $config['option'] = 'com_foftest';

        $model = new F0FModel($config);

        // I prefer using the reflection class instead of the setter method, so I can be sure of what is going on
        $reflect  = new ReflectionClass($model);
        $property = $reflect->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($model, 88);

        $value = $model->getId();

        $this->assertEquals(88, $value, 'F0FModel::getId Wrong set value');
    }

    /**
     * @group               modelTestSetIdsFromRequest
     * @group               F0FModel
     * @covers              F0FModel::setIDsFromRequest
     * @dataProvider        getTestSetIDsFromRequest
     * @preventDataLoading
     */
    public function testSetIDsFromRequest($modelinfo, $test, $checks)
    {
        $config['option'] = 'com_foftest';
        $config['view']   = $modelinfo['name'];
        $input            = array();

        if(isset($test['cid']))
        {
            $input['cid'] = $test['cid'];
        }

        if(isset($test['id']))
        {
            $input['id'] = $test['id'];
        }

        if(isset($test['kid']))
        {
            $input[$test['kid']['name']] = $test['kid']['value'];
        }

        // F0FModel constructor will automatically set the data coming from the request, taking the from the request
        // (which is bad for our test), so I have to use the getTmp method.
        // Sadly, it will reset the input, too, so I have to manually inject it.
        $model = F0FModel::getTmpInstance($modelinfo['name'], 'FoftestModel', $config);
        $model->setInput(new F0FInput($input));

        $model->setIDsFromRequest();

        $property = new ReflectionProperty($model, 'id');
        $property->setAccessible(true);
        $this->assertEquals($checks['id'], $property->getValue($model), 'F0FModel::setIDsFromRequests wrong value for property "id"');

        $property = new ReflectionProperty($model, 'id_list');
        $property->setAccessible(true);
        $this->assertEquals($checks['id_list'], $property->getValue($model), 'F0FModel::setIDsFromRequests wrong value for property "id_list"');
    }

	/**
	 * @group               modelTestSetId
	 * @group               F0FModel
	 * @covers              F0FModel::setId
	 * @dataProvider        getTestSetId
	 * @preventDataLoading
	 */
	public function testSetId($modelId)
	{
		$config['option'] = 'com_foftest';

		$model = new F0FModel($config);
		$rc = $model->setId($modelId);

		$this->assertInstanceOf('\\FOF30\\Model\\Model', $rc, 'F0FModel::setId should return itself in order to support chaining');

		$reflect  = new ReflectionClass($model);
		$property = $reflect->getProperty('id');
		$property->setAccessible(true);
		$value    = $property->getValue($model);

        if(is_array($modelId))
        {
            $expected = array_shift($modelId);
        }
        else
        {
            $expected = $modelId;
        }

		$this->assertEquals($expected, $value, 'F0FModel::setId Wrong set value');
	}

	/**
	 * @group               modelTestSetIdException
	 * @group               F0FModel
	 * @covers              F0FModel::setId
	 * @dataProvider        getTestSetIdException
	 * @preventDataLoading
	 */
	public function testSetIdException($modelId)
	{
		$this->setExpectedException('InvalidArgumentException');

		$config['option'] = 'com_foftest';

		$model = new F0FModel($config);
		$model->setId($modelId);
	}

    /**
     * @group               modelTestSetIds
     * @group               F0FModel
     * @covers              F0FModel::setIds
     * @dataProvider        getTestSetIds
     * @preventDataLoading
     */
    public function testSetIds($modelIds, $check)
    {
        $config['option'] = 'com_foftest';

        $model = new F0FModel($config);
        $rc = $model->setIds($modelIds);

        $this->assertInstanceOf('\\FOF30\\Model\\Model', $rc, 'F0FModel::setIds should return itself in order to support chaining');

        $reflect  = new ReflectionClass($model);

        $property = $reflect->getProperty('id');
        $property->setAccessible(true);
        $value    = $property->getValue($model);

        $this->assertEquals($check['id'], $value, 'F0FModel::setIds Wrong value for "id" property');

        $property = $reflect->getProperty('id_list');
        $property->setAccessible(true);
        $value    = $property->getValue($model);

        $this->assertEquals($check['id_list'], $value, 'F0FModel::setIds Wrong value for "id_list" property');
    }

    /**
     * @group               modelTestReset
     * @group               F0FModel
     * @covers              F0FModel::reset
     * @preventDataLoading
     */
    public function testReset()
    {
        $config['option'] = 'com_foftest';

        $model = new F0FModel($config);

        $toBeInspected = array(
            'id'         => array('expected' => 0),
            'id_list'    => array('expected' => null),
            'record'     => array('expected' => null),
            'list'       => array('expected' => null),
            'pagination' => array('expected' => null),
            'total'      => array('expected' => null),
            'otable'     => array('expected' => null)
        );

        $reflect  = new ReflectionClass($model);

        foreach($toBeInspected as $property => &$info)
        {
            $t = $reflect->getProperty($property);
            $t->setAccessible(true);

            $t->setValue($model, 'dummy value');

            $info['reflection'] = $t;
        }

        $model->reset();

        foreach($toBeInspected as $property => &$info)
        {
            $value = $info['reflection']->getValue($model);

            $this->assertEquals($info['expected'], $value, '');
        }
    }

    /**
     * @group               modelTestGetItem
     * @group               F0FModel
     * @covers              F0FModel::getItem
     * @dataProvider        getTestGetItem
     */
    public function testGetItem($modelinfo, $test, $session, $checks)
    {
        $config['option'] = 'com_foftest';
        $config['view']   = $modelinfo['name'];
        $model = F0FModel::getTmpInstance($modelinfo['name'], 'FoftestModel', $config);
		$model->savestate(true); // This is required to emulate the save state flag set by getAnInstance, used by the Controller

        if($test['setid'])
        {
            $model->setId($test['setid']);
        }

        if($session)
        {
            $mockSession = $this->getMock('JSession', array('get'));
            $mockSession->expects($this->any())->method('get')->will($this->returnValue($session));

            JFactory::$session = $mockSession;
        }

        $result = $model->getItem($test['id']);

        $this->assertInstanceOf('\\FOF30\\Table\\Table', $result, 'F0FModel::getItem should return an instance of F0FTable');

        foreach($checks as $property => $value)
        {
            $this->assertEquals($value, $result->$property, 'F0FModel::getItem loaded the wrong data for property '.$property);
        }
    }

    /**
     * Tailored test to check when the session has a different id from the loaded from db one
     *
     * @group               modelTestGetItem
     * @group               F0FModel
     * @covers              F0FModel::getItem
     */
    public function testGetItemSessionWipe()
    {
        $config['option'] = 'com_foftest';
        $config['view']   = 'foobars';

        $model = F0FModel::getTmpInstance('Foobars', 'FoftestModel', $config);
		$model->savestate(true); // Required to emulate the save state flag set by getAnInstance used by the Controller

        $hackedSession = new JSession;

        // Manually set the session as active
        $property = new ReflectionProperty($hackedSession, '_state');
        $property->setAccessible(true);
        $property->setValue($hackedSession, 'active');

        $session = serialize(array('foftest_foobar_id' => 0, 'title' => 'Title from session'));

        // We're in CLI an no $_SESSION variable? No problem, I'll manually create it!
        // I'm going to hell for doing this...
        $_SESSION['__default']['com_foftest.cpanels.savedata'] = $session;

        JFactory::$session = $hackedSession;

        $result = $model->getItem(2);

        $this->assertInstanceOf('\\FOF30\\Table\\Table', $result, 'F0FModel::getItem should return an instance of F0FTable');

		$result = $model->save($result->getData());

		$this->assertEquals($result, true, 'F0FModel::save failed');

        $this->assertArrayNotHasKey('com_foftest.cpanels.savedata', $_SESSION['__default'], 'F0FModel::save should wipe saved session data');

        // Let's remove any evidence...
        unset($_SESSION);
    }

    /**
     * @group               modelTestGetItemList
     * @group               F0FModel
     * @covers              F0FModel::getItemList
     * @dataProvider        getTestGetItemList
     */
    public function testGetItemList($modelinfo, $test, $checks)
    {
        $config['input']  = array(
            'option'    => 'com_foftest',
            'view'      => $modelinfo['name']
        );

        $model = $this->getMock('\\FOF30\\Model\\Model', array('buildQuery'), array($config));

        $model->expects($this->any())->method('buildQuery')->will($this->returnValue($test['query']));
        $model->limit($test['limit']);
        $model->limitstart($test['limitstart']);

        $list = $model->getItemList($test['override'], $test['group']);

        $this->assertEquals($checks['list'], $list, 'F0FModel::getItemList return a wrong recordset');
    }

    /**
     * Tailored test to check if getItemList is using the internal cache, instead of running the query again
     *
     * @group               modelTestGetItemListCache
     * @group               F0FModel
     * @covers              F0FModel::getItemList
     * @dataProvider        getTestGetItemList
     * @preventDataLoading
     */
    public function testGetItemListCache()
    {
        $config['option'] = 'com_foftest';
        $config['view']   = 'foobars';
        $model = F0FModel::getTmpInstance('Foobars', 'FoftestModel', $config);

        $dummy = array('Hijacked internal cache');

        $property = new ReflectionProperty($model, 'list');
        $property->setAccessible(true);
        $property->setValue($model, $dummy);

        $result = $model->getItemList();

        $this->assertEquals($dummy, $result, 'F0FModel::getItemList failed to use its internal cache');
    }

    /**
     * @group               modelTestGetIterator
     * @group               F0FModel
     * @covers              F0FModel::getIterator
     * @dataProvider        getTestGetIterator
     */
    public function testGetIterator($test, $checks)
    {
        $config['option'] = 'com_foftest';
        $config['view']   = 'foobars';
        $model = F0FModel::getTmpInstance('Foobars', 'FoftestModel', $config);

        if($test['limit'] && $test['limitstart'])
        {
            $model->setState('limit', $test['limit']);
            $model->setState('limitstart', $test['limitstart']);
        }

        $iterator = $model->getIterator($test['override'], $test['tableClass']);
        $item     = $iterator->current();

        $this->assertInstanceOf('F0FDatabaseIterator', $iterator, 'F0FModel::getIterator should return an instance of F0FDatabaseIterator');
        $this->assertEquals($checks['count'], count($iterator), 'F0FModel::getIterator did not respect the limits');
        $this->assertInstanceOf($checks['tableClass'], $item, 'F0FModel::getIterator used the wrong class for record loading');
    }

    /**
     * @group               modelTestSave
     * @group               F0FModel
     * @covers              F0FModel::save
     * @dataProvider        getTestSave
     */
    public function testSave($test, $checks)
    {
        $db           = JFactory::getDbo();
        $tableConstr  = array('jos_foftest_foobars', 'foftest_foobar_id', &$db);

        // F0FTable mock
        $table = $this->getMock('\\FOF30\\Table\\Table',	array('save', 'getErrors'), $tableConstr, '',	true, true, true, true);

        $table->expects($this->any())->method('save')->will($this->returnCallback(
            function() use(&$table, $test)
            {
                if(isset($test['table']['save']['id']))
                {
                    // The save method assigns an id to the table, so I have to do the same thing
                    $table->foftest_foobar_id = $test['table']['save']['id'];
                }

                return $test['table']['save']['return'];
            }
        ));

        // onBefore event changes data, I want to check that the table gets the correct one
        if(isset($test['onBefore']['modify']))
        {
            $table->expects($this->any())->method('save')->with($checks['save']['data']);
        }

        $table->expects($this->any())->method('getErrors')->will($this->returnValue($test['table']['error']));

        // F0FForm mock
        if(isset($test['form']['mock']))
        {
            $form = $this->getMock('\\FOF30\\Form\\Form', array('getAttribute'), array('dummy'));
            $form->expects($this->any())->method('getAttribute')->will($this->returnValue($test['form']['validation']));
        }
        else
        {
            $form = $test['form'];
        }

        // F0FModel mock
        $config['input'] = array('option' => 'com_foftest', 'view' => 'foobars');
        $modelMethods    = array('getForm', 'onBeforeSave', 'onAfterSave', 'getTable', 'validateForm');
        $model           = $this->getMock('\\FOF30\\Model\\Model', $modelMethods, array($config));

        $model->expects($this->any())->method('getForm')->will($this->returnValue($form));

        if(isset($test['form']['validationResult']))
        {
            $model->expects($this->any())->method('validateForm')->will($this->returnValue($test['form']['validationResult']));
        }

        $model->expects($this->any())->method('onBeforeSave')
              ->will($this->returnCallback(
                function(&$allData, &$table) use ($test)
                {
                    if(isset($test['onBefore']['modify']))
                    {
                        $allData = $test['onBefore']['allData'];
                        $table   = $test['onBefore']['table']($table);
                    }

                    return $test['onBefore']['return'];
                }
            ));

        $model->expects($this->any())->method('onAfterSave')->will($this->returnValue($test['onAfter']));
        $model->expects($this->any())->method('getTable')->will($this->returnValue($table));

        if(isset($test['loadid']))
        {
            $test['data']->load($test['loadid']);
        }

        $return = $model->save($test['data']);

        $this->assertEquals($checks['return'], $return, 'F0FModel::save returned the wrong value');

        if($checks['return'])
        {
            $savedTable = $table;

            $property = new ReflectionProperty($model, 'id');
            $property->setAccessible(true);
            $tableId  = $property->getValue($model);

            $this->assertEquals($checks['table']['id'], $tableId, 'F0FModel::save internally saved the wrong table id');
        }
        else
        {
            $savedTable = null;
        }

        $otable = new ReflectionProperty($model, 'otable');
        $otable->setAccessible(true);
        $otable = $otable->getValue($model);

        $this->assertEquals($savedTable, $otable, 'F0FModel::save internally saved the wrong table');
    }

    /**
     * @group               modelTestSave
     * @group               F0FModel
     * @covers              F0FModel::save
     * @dataProvider        getTestSaveSessionWipe
     */
    public function testSaveSessionWipe($test, $checks)
    {
        $hackedSession = new JSession;

        // Manually set the session as active
        $property = new ReflectionProperty($hackedSession, '_state');
        $property->setAccessible(true);
        $property->setValue($hackedSession, 'active');

        // We're in CLI an no $_SESSION variable? No problem, I'll manually create it!
        // I'm going to hell for doing this... (again)
        $_SESSION['__default']['com_foftest.foobars.savedata'] = '';

        JFactory::$session = $hackedSession;

        $db           = JFactory::getDbo();
        $tableConstr  = array('jos_foftest_foobars', 'foftest_foobar_id', &$db);

        // F0FTable mock
        $table = $this->getMock('\\FOF30\\Table\\Table',	array('save', 'getErrors', 'getProperties'), $tableConstr, '', true, true, true, true);
        $table->expects($this->any())->method('save')->will($this->returnValue($test['table']['save']));
        $table->expects($this->any())->method('getErrors')->will($this->returnValue($test['table']['error']));
        $table->expects($this->any())->method('getProperties')->will($this->returnValue($test['table']['properties']));

        // F0FModel mock
        $config['input'] = array('option' => 'com_foftest', 'view' => 'foobars');
        $modelMethods    = array('getTable');
        $model           = $this->getMock('\\FOF30\\Model\\Model', $modelMethods, array($config));
        $model->expects($this->any())->method('getTable')->will($this->returnValue($table));

        $return = $model->save($test['data']);

        $this->assertEquals($checks['return'], $return, 'F0FModel::save returned the wrong value');

        if($checks['return'])
        {
            $this->assertArrayNotHasKey('com_foftest.foobars.savedata', $_SESSION['__default'], 'F0FModel::save should wipe saved session data');
        }
        else
        {
            $this->assertArrayHasKey('com_foftest.foobars.savedata', $_SESSION['__default'], 'F0FModel::save should not wipe saved session data when failing');
            $this->assertEquals($checks['session'], $_SESSION['__default']['com_foftest.foobars.savedata'], 'F0FModel::save stored the wrong data in the session after failing');
        }

        // Let's remove any evidence...
        unset($_SESSION);
    }

    /**
     * In the following test I'll mock almost everything: this because I don't care about the database interaction,
     * I just want to test the model in all the possible scenarios.
     *
     * @group               modelTestCopy
     * @group               F0FModel
     * @covers              F0FModel::copy
     * @dataProvider        getTestCopy
     * @preventDataLoading
     */
    public function testCopy($test, $checks)
    {
        $config['input'] = array('option' => 'com_foftest', 'view' => 'foobars');

        $db = JFactory::getDbo();
        $constr_args = array('jos_foftest_foobars', 'foftest_foobar_id', &$db);

        $table = $this->getMock('\\FOF30\\Table\\Table',	array('copy', 'getError'), $constr_args, '',	true, true, true, true);
        $table->expects($this->any())->method('copy')->will($this->returnValue($test['copy']));
        $table->expects($this->any())->method('getError')->will($this->returnValue($test['error']));

        $model = $this->getMock('\\FOF30\\Model\\Model', array('onBeforeCopy', 'onAfterCopy', 'getTable'), array($config));
        $model->expects($this->any())->method('onBeforeCopy')->will($this->returnValue($test['onBefore']));
        $model->expects($this->any())->method('onAfterCopy')->will($this->returnValue($test['onAfter']));
        $model->expects($this->any())->method('getTable')->will($this->returnValue($table));

        if(isset($test['id_list']))
        {
            $property = new ReflectionProperty($model, 'id_list');
            $property->setAccessible(true);
            $property->setValue($model, $test['id_list']);
        }

        $return = $model->copy();

        $this->assertEquals($checks['return'], $return, 'F0FModel::copy returned a wrong value');

        if(!$test['copy'])
        {
            $this->assertEquals($test['error'], $model->getError(), 'F0FModel::copy got the wrong error message when copy fails');
        }
    }

    /**
     * In the following test I'll mock almost everything: this because I don't care about the database interaction,
     * I just want to test the model in all the possible scenarios.
     *
     * @group               modelTestDelete
     * @group               F0FModel
     * @covers              F0FModel::delete
     * @dataProvider        getTestDelete
     * @preventDataLoading
     */
    public function testDelete($test, $checks)
    {
        $config['input'] = array('option' => 'com_foftest', 'view' => 'foobars');

        $db = JFactory::getDbo();
        $constr_args = array('jos_foftest_foobars', 'foftest_foobar_id', &$db);

        $table = $this->getMock('\\FOF30\\Table\\Table',	array('delete', 'getError'), $constr_args, '', true, true, true, true);
        $table->expects($this->any())->method('delete')->will($this->returnValue($test['delete']));
        $table->expects($this->any())->method('getError')->will($this->returnValue($test['error']));

        $model = $this->getMock('\\FOF30\\Model\\Model', array('onBeforeDelete', 'onAfterDelete', 'getTable'), array($config));

        if(isset($test['beforeFailsOnce']))
        {
            $model->expects($this->any())->method('onBeforeDelete')->will($this->onConsecutiveCalls(false, true));
        }
        else
        {
            $model->expects($this->any())->method('onBeforeDelete')->will($this->returnValue($test['onBefore']));
        }

        $model->expects($this->any())->method('onAfterDelete')->will($this->returnValue($test['onAfter']));
        $model->expects($this->any())->method('getTable')->will($this->returnValue($table));

        if(isset($test['id_list']))
        {
            $property = new ReflectionProperty($model, 'id_list');
            $property->setAccessible(true);
            $property->setValue($model, $test['id_list']);
        }

        $return = $model->delete();

        $this->assertEquals($checks['return'], $return, 'F0FModel::delete returned a wrong value');

        if(!$test['delete'])
        {
            $this->assertEquals($test['error'], $model->getError(), 'F0FModel::delete got the wrong error message when delete fails');
        }
    }

    /**
     * In the following test I'll mock almost everything: this because I don't care about the database interaction,
     * I just want to test the model in all the possible scenarios.
     *
     * @group               modelTestPublish
     * @group               F0FModel
     * @covers              F0FModel::publish
     * @dataProvider        getTestPublish
     * @preventDataLoading
     */
    public function testPublish($test, $checks)
    {
        $config['option'] = 'com_foftest';
        $config['name']   = 'foobars';
        $config['input']  = array('option' => 'com_foftest', 'view' => 'foobars');

        $db = JFactory::getDbo();
        $constr_args = array('jos_foftest_foobars', 'foftest_foobar_id', &$db);

        $table = $this->getMock('\\FOF30\\Table\\Table',	array('publish', 'getError'), $constr_args, '', true, true, true, true);
        $table->expects($this->any())->method('publish')->will($this->returnValue($test['publish']));
        $table->expects($this->any())->method('getError')->will($this->returnValue($test['error']));

        $model = $this->getMock('\\FOF30\\Model\\Model', array('onBeforePublish', 'onAfterPublish', 'getTable'), array($config));
        $model->expects($this->any())->method('onBeforePublish')->will($this->returnValue($test['onBefore']));
        $model->expects($this->any())->method('onAfterPublish')->will($this->returnValue($test['onAfter']));
        $model->expects($this->any())->method('getTable')->will($this->returnValue($table));

        $model->setInput($config['input']);

        if(isset($test['id_list']))
        {
            $property = new ReflectionProperty($model, 'id_list');
            $property->setAccessible(true);
            $property->setValue($model, $test['id_list']);
        }

        if($test['publish'])
        {
            $property = new ReflectionProperty($model, 'event_change_state');
            $property->setAccessible(true);
            $event = $property->getValue($model);

            // Let's create a mock for the platform and check that plugins are run
            $platform = $this->getMock('\\FOF30\\Integration\\Joomla\\Platform', array('runPlugins'));
            $platform->expects($this->any())->method('runPlugins')->with(
                $event,
                array('com_foftest.foobars', $test['id_list'], 1)
            );

            F0FPlatform::forceInstance($platform);
        }

        // I don't pass any argument since I'm not interested in records being really published, that's table duty.
        // Here I'm testing how the model reacts vs different scenarios
        $return = $model->publish();

        $this->assertEquals($checks['return'], $return, 'F0FModel::publish returned a wrong value');

        if(!$test['publish'])
        {
            $this->assertEquals($test['error'], $model->getError(), 'F0FModel::publish got the wrong error message when publish fails');
        }
    }

    /**
     * In the following test I'll mock almost everything: this because I don't care about the database interaction,
     * I just want to test the model in all the possible scenarios.
     *
     * @group               modelTestCheckout
     * @group               F0FModel
     * @covers              F0FModel::checkout
     * @dataProvider        getTestCheckout
     * @preventDataLoading
     */
    public function testCheckout($test, $checks)
    {
        $config['input'] = array('option' => 'com_foftest', 'view' => 'foobars');

        $db = JFactory::getDbo();
        $constr_args = array('jos_foftest_foobars', 'foftest_foobar_id', &$db);

        $table = $this->getMock('\\FOF30\\Table\\Table',	array('checkout', 'getError'), $constr_args, '',	true, true, true, true);
        $table->expects($this->any())->method('checkout')->will($this->returnValue($test['checkout']));
        $table->expects($this->any())->method('getError')->will($this->returnValue($test['error']));

        $model = $this->getMock('\\FOF30\\Model\\Model', array('getTable'), array($config));
        $model->expects($this->any())->method('getTable')->will($this->returnValue($table));

        $return = $model->checkout();

        $this->assertEquals($checks['return'], $return, 'F0FModel::checkout returned a wrong value');

        if(!$test['checkout'])
        {
            $this->assertEquals($test['error'], $model->getError(), 'F0FModel::checkout got the wrong error message when checkout fails');
        }
    }

    /**
     * In the following test I'll mock almost everything: this because I don't care about the database interaction,
     * I just want to test the model in all the possible scenarios.
     *
     * @group               modelTestCheckin
     * @group               F0FModel
     * @covers              F0FModel::checkin
     * @dataProvider        getTestCheckin
     * @preventDataLoading
     */
    public function testCheckin($test, $checks)
    {
        $config['input'] = array('option' => 'com_foftest', 'view' => 'foobars');

        $db = JFactory::getDbo();
        $constr_args = array('jos_foftest_foobars', 'foftest_foobar_id', &$db);

        $table = $this->getMock('\\FOF30\\Table\\Table',	array('checkin', 'getError'), $constr_args, '',	true, true, true, true);
        $table->expects($this->any())->method('checkin')->will($this->returnValue($test['checkin']));
        $table->expects($this->any())->method('getError')->will($this->returnValue($test['error']));

        $model = $this->getMock('\\FOF30\\Model\\Model', array('getTable'), array($config));
        $model->expects($this->any())->method('getTable')->will($this->returnValue($table));

        $return = $model->checkin();

        $this->assertEquals($checks['return'], $return, 'F0FModel::checkin returned a wrong value');

        if(!$test['checkin'])
        {
            $this->assertEquals($test['error'], $model->getError(), 'F0FModel::checkin got the wrong error message when checkin fails');
        }
    }

    /**
     * In the following test I'll mock almost everything: this because I don't care about the database interaction,
     * I just want to test the model in all the possible scenarios.
     *
     * @group               modelTestIsCheckedOut
     * @group               F0FModel
     * @covers              F0FModel::isCheckedOut
     * @dataProvider        getTestIsCheckedOut
     * @preventDataLoading
     */
    public function testIsCheckedOut($test, $checks)
    {
        $config['input'] = array('option' => 'com_foftest', 'view' => 'foobars');

        $db = JFactory::getDbo();
        $constr_args = array('jos_foftest_foobars', 'foftest_foobar_id', &$db);

        $table = $this->getMock('\\FOF30\\Table\\Table',	array('isCheckedOut', 'getError'), $constr_args, '', true, true, true, true);
        $table->expects($this->any())->method('isCheckedOut')->will($this->returnValue($test['isCheckedOut']));
        $table->expects($this->any())->method('getError')->will($this->returnValue($test['error']));

        $model = $this->getMock('\\FOF30\\Model\\Model', array('getTable'), array($config));
        $model->expects($this->any())->method('getTable')->will($this->returnValue($table));

        $return = $model->isCheckedOut();

        $this->assertEquals($checks['return'], $return, 'F0FModel::isCheckedOut returned a wrong value');

        if(!$test['isCheckedOut'])
        {
            $this->assertEquals($test['error'], $model->getError(), 'F0FModel::isCheckedOut got the wrong error message when isCheckedOut fails');
        }
    }

    /**
     * In the following test I'll mock almost everything: this because I don't care about the database interaction,
     * I just want to test the model in all the possible scenarios.
     *
     * @group               modelTestHit
     * @group               F0FModel
     * @covers              F0FModel::hit
     * @dataProvider        getTestHit
     * @preventDataLoading
     */
    public function testHit($test, $checks)
    {
        $config['input'] = array('option' => 'com_foftest', 'view' => 'foobars');

        $db = JFactory::getDbo();
        $constr_args = array('jos_foftest_foobars', 'foftest_foobar_id', &$db);

        $table = $this->getMock('\\FOF30\\Table\\Table',	array('hit', 'getError'), $constr_args, '', true, true, true, true);
        $table->expects($this->any())->method('hit')->will($this->returnValue($test['hit']));
        $table->expects($this->any())->method('getError')->will($this->returnValue($test['error']));

        $model = $this->getMock('\\FOF30\\Model\\Model', array('onBeforeHit', 'onAfterHit', 'getTable'), array($config));
        $model->expects($this->any())->method('onBeforeHit')->will($this->returnValue($test['onBefore']));
        $model->expects($this->any())->method('onAfterHit')->will($this->returnValue($test['onAfter']));
        $model->expects($this->any())->method('getTable')->will($this->returnValue($table));

        if(isset($test['id_list']))
        {
            $property = new ReflectionProperty($model, 'id_list');
            $property->setAccessible(true);
            $property->setValue($model, $test['id_list']);
        }

        $return = $model->hit();

        $this->assertEquals($checks['return'], $return, 'F0FModel::hit returned a wrong value');

        if(!$test['hit'])
        {
            $this->assertEquals($test['error'], $model->getError(), 'F0FModel::hit got the wrong error message when hit fails');
        }
    }

    /**
     * In the following test I'll mock almost everything: this because I don't care about the database interaction,
     * I just want to test the model in all the possible scenarios.
     *
     * @group               modelTestMove
     * @group               F0FModel
     * @covers              F0FModel::move
     * @dataProvider        getTestMove
     * @preventDataLoading
     */
    public function testMove($test, $checks)
    {
        $config['input'] = array('option' => 'com_foftest', 'view' => 'foobars');

        $db = JFactory::getDbo();
        $constr_args = array('jos_foftest_foobars', 'foftest_foobar_id', &$db);

        $table = $this->getMock('\\FOF30\\Table\\Table',	array('move', 'load', 'getError'), $constr_args, '', true, true, true, true);
        $table->expects($this->any())->method('move')->will($this->returnValue($test['move']));
        $table->expects($this->any())->method('load')->will($this->returnValue($test['load']));
        $table->expects($this->any())->method('getError')->will($this->returnValue($test['error']));

        $model = $this->getMock('\\FOF30\\Model\\Model', array('onBeforeMove', 'onAfterMove', 'getTable'), array($config));
        $model->expects($this->any())->method('onBeforeMove')->will($this->returnValue($test['onBefore']));
        $model->expects($this->any())->method('onAfterMove')->will($this->returnValue($test['onAfter']));
        $model->expects($this->any())->method('getTable')->will($this->returnValue($table));

        // I don't pass any argument since I'm not interested in records being really published, that's table duty.
        // Here I'm testing how the model reacts vs different scenarios
        $return = $model->move(1);

        $this->assertEquals($checks['return'], $return, 'F0FModel::move returned a wrong value');

        if(!$test['move'])
        {
            $this->assertEquals($test['error'], $model->getError(), 'F0FModel::move got the wrong error message when move fails');
        }
    }

    /**
     * In the following test I'll mock almost everything: this because I don't care about the database interaction,
     * I just want to test the model in all the possible scenarios.
     *
     * @group               modelTestReorder
     * @group               F0FModel
     * @covers              F0FModel::reorder
     * @dataProvider        getTestReorder
     * @preventDataLoading
     */
    public function testReorder($test, $checks)
    {
        $config['input'] = array('option' => 'com_foftest', 'view' => 'foobars');

        $db = JFactory::getDbo();
        $constr_args = array('jos_foftest_foobars', 'foftest_foobar_id', &$db);

        $table = $this->getMock('\\FOF30\\Table\\Table',	array('reorder', 'getError'), $constr_args, '',	true, true, true, true);
        $table->expects($this->any())->method('reorder')->will($this->returnValue($test['reorder']));
        $table->expects($this->any())->method('getError')->will($this->returnValue($test['error']));

        $model = $this->getMock('\\FOF30\\Model\\Model', array('onBeforeReorder', 'onAfterReorder', 'getTable'), array($config));
        $model->expects($this->any())->method('onBeforeReorder')->will($this->returnValue($test['onBefore']));
        $model->expects($this->any())->method('onAfterReorder')->will($this->returnValue($test['onAfter']));
        $model->expects($this->any())->method('getTable')->will($this->returnValue($table));

        $return = $model->reorder();

        $this->assertEquals($checks['return'], $return, 'F0FModel::reorder returned a wrong value');

        if(!$test['reorder'])
        {
            $this->assertEquals($test['error'], $model->getError(), 'F0FModel::reorder got the wrong error message when reorder fails');
        }
    }

    /**
     * @group               modelTestgetTotal
     * @group               F0FModel
     * @covers              F0FModel::getTotal
     * @dataProvider        getTestgetTotal
     */
    public function testGetTotal($test, $checks)
    {
        $config['input'] = array('option' => 'com_foftest', 'view' => 'foobars');

        $model = $this->getMock('\\FOF30\\Model\\Model', array('buildCountQuery', 'buildQuery'), array($config));
        $model->expects($this->any())->method('buildCountQuery')->will($this->returnValue($test['buildCount']));
        $model->expects($this->any())->method('buildQuery')->will($this->returnValue($test['buildQuery']));

        $total = $model->getTotal();

        $this->assertEquals($checks['total'], $total, 'F0FModel::getTotal returned wrong total value');
    }

    /**
     * @group               modelTestGetHash
     * @group               F0FModel
     * @covers              F0FModel::getHash
     * @dataProvider        getTestGetHash
     * @preventDataLoading
     */
    public function testGetHash($modelinfo, $test, $checks)
    {
        $config['input'] = array(
            'option' => 'com_foftest',
            'view'   => $modelinfo['name']
        );

        if(isset($test['tmpInstance']) && $test['tmpInstance'])
        {
            $model = F0FModel::getTmpInstance('Foobars', 'FoftestModel', $config);
        }
        else
        {
            $model = F0FModel::getAnInstance('Foobars', 'FoftestModel', $config);
        }

        $hash = $model->getHash();

        $this->assertEquals($checks['hash'], $hash, 'F0FModel::getHash created a wrong hash value');
    }

    /**
     * @group               modelTestGetList
     * @group               F0FModel
     * @covers              F0FModel::_getList
     * @covers              F0FModel::onProcessList
     * @dataProvider        getTestGetList
     */
    public function testGetList($modelinfo, $test, $checks)
    {
        $config['input']  = array(
            'option'    => 'com_foftest',
            'view'      => $modelinfo['name']
        );

        // Create a mock so I can test onProcessList, too
        $model = $this->getMock('\\FOF30\\Model\\Model', array('onProcessList'), array($config));
        $model->expects($this->any())->method('onProcessList')->will($this->returnCallback($test['callback']));

        $method = new ReflectionMethod($model, '_getList');
        $method->setAccessible(true);
        $list = $method->invoke($model, $test['query'], $test['limitstart'], $test['limit'], $test['group']);

        $this->assertEquals($checks['list'], $list, 'F0FModel::_getList returned a wrong recordset');
    }

    /**
     * In this test I will simply check that the invocation of the _createTable is made with the correct
     * arguments. I will check for the correct table to be returned while testing _createTable.
     *
     * @group               modelTestGetTable
     * @group               F0FModel
     * @covers              F0FModel::getTable
     * @dataProvider        getTestGetTable
     * @preventDataLoading
     */
    public function testGetTable($modelinfo, $test)
    {
        // This is a workaround for dealing with mocked objects. When checking the arguments passed to _createTable
        // the return value is NULL, and this throws an exception. However, this is the expected behavior. We can't simply
        // wrap everything with a try-catch statement since it will prevent PHPUnit from notifing us of errors
        $this->setExpectedException('Exception', 0);

        $config['option'] = 'com_foftest';
        $config['name']   = $modelinfo['name'];
        $config['table']  = F0FInflector::singularize($modelinfo['name']);
        $config['input']  = array('option' => 'com_foftest', 'view' => $modelinfo['name']);

        $model = $this->getMock('\\FOF30\\Model\\Model', array('_createTable'), array($config));

        if(!$test['create']['options'])
        {
            $reflection = new ReflectionProperty($model, 'input');
            $reflection->setAccessible(true);

            $test['create']['options'] = array('input' => $reflection->getValue($model));
        }

        if(isset($test['wipeTable']))
        {
            $reflection = new ReflectionProperty($model, 'table');
            $reflection->setAccessible(true);
            $reflection->setValue($model, null);
        }

        $model->expects($this->any())
              ->method('_createTable')
              ->with(
                $test['create']['name'],
                $test['create']['prefix'],
                $test['create']['options']
            );

        $table = $model->getTable($test['name'], $test['prefix'], $test['options']);
    }

    /**
     * @group               modelTestGetTable
     * @group               F0FModel
     * @covers              F0FModel::getTable
     * @preventDataLoading
     */
    public function testGetTableException()
    {
        $this->setExpectedException('Exception', 0);

        $config['input']  = array(
            'option'    => 'com_foftest',
            'view'      => 'foobars'
        );

        $model = $this->getMock('\\FOF30\\Model\\Model', array('_createTable'), array($config));
        $model->expects($this->any())->method('_createTable')->will($this->returnValue(false));

        $model->getTable();
    }

    /**
     * @group               modelTestCreateTable
     * @group               F0FModel
     * @covers              F0FModel::_createTable
     * @dataProvider        getTestCreateTable
     * @preventDataLoading
     */
    public function testCreateTable($modelinfo, $test, $checks)
    {
        $config['input']  = array(
            'option'    => 'com_foftest',
            'view'      => $modelinfo['name']
        );

        $model = F0FModel::getTmpInstance($modelinfo['name'], 'FoftestModel', $config);

        // I have to get the SAME dbo object, or the table won't be the same
        if(isset($test['loadDbo']))
        {
            $test['config']['dbo'] = $model->getDbo();
        }

        $method = new ReflectionMethod($model, '_createTable');
        $method->setAccessible(true);
        $table = $method->invoke($model, $test['name'], $test['prefix'], $test['config']);

        // Let's reset any saved instance
        F0FTable::forceInstance();

        $tableCheck = F0FTable::getAnInstance($checks['name'], $checks['prefix'], array('dbo' => $model->getDbo()));

        $this->assertInstanceOf('\\FOF30\\Table\\Table', $table, 'F0FModel::_createTable should return an instance of F0FTable');
        $this->assertEquals($tableCheck, $table, 'F0FModel::_createTable returned a wrong table');
    }

    /**
     * @group               modelTestBuildQuery
     * @group               F0FModel
     * @covers              F0FModel::buildQuery
     * @dataProvider        getTestBuildQuery
     * @preventDataLoading
     */
    public function testBuildQuery($modelinfo, $test, $checks)
    {
        $config['option'] = 'com_foftest';
        $config['name']   = $modelinfo['name'];
        $config['table']  = F0FInflector::singularize($modelinfo['name']);
        $config['input']  = array('option' => 'com_foftest', 'view' => $modelinfo['name']);

        // Create a mock so I can test vs different table alias
        $model = $this->getMock('\\FOF30\\Model\\Model', array('getTableAlias'), array($config));
        $model->expects($this->any())->method('getTableAlias')->will($this->returnValue($test['aliasTable']));

        // Let's create a mocked Behavior, so I can manipulate its behavior (LOL)
        $behavior = $this->getMock('\\FOF30\\Model\\Dispatcher\\Behavior', array('trigger'));
        $behavior->expects($this->any())->method('trigger')->will($this->returnValue(null));

        // Inject the hacked behavior
        $property = new ReflectionProperty($model, 'modelDispatcher');
        $property->setAccessible(true);
        $property->setValue($model, $behavior);

        $query = $model->buildQuery($test['overrideLimits']);

        $this->assertEquals((string) $checks['query'], (string) $query, 'F0FModel::buildQuery returned a wrong query');
    }

    /**
     * @group               modelTest__get
     * @group               F0FModel
     * @covers              F0FModel::__get
     * @preventDataLoading
     */
    public function test__get()
    {
        $config['input']  = array('option' => 'com_foftest', 'view' => 'foobars');

        $model = F0FModel::getTmpInstance('Foobars', 'FoftestModel', $config);
        $model->setState('dummy', 'test');

        $this->assertEquals('test', $model->dummy, 'F0FModel::__get failed to retrieve a state using magic getter');
    }

    /**
     * @group               modelTest__set
     * @group               F0FModel
     * @covers              F0FModel::__set
     * @preventDataLoading
     */
    public function test__set()
    {
        $config['input']  = array('option' => 'com_foftest', 'view' => 'foobars');

        $model = F0FModel::getTmpInstance('Foobars', 'FoftestModel', $config);
        $model->dummy = 'test';

        $this->assertEquals('test', $model->getState('dummy'), 'F0FModel::__set failed to retrieve a state using magic setter');
    }

    /**
     * @group               modelTest__call
     * @group               F0FModel
     * @covers              F0FModel::__call
     * @preventDataLoading
     */
    public function test__call()
    {
        $config['input']  = array('option' => 'com_foftest', 'view' => 'foobars');

        $model = F0FModel::getTmpInstance('Foobars', 'FoftestModel', $config);
        $model->dummy('test');

        $this->assertEquals('test', $model->getState('dummy'), 'F0FModel::__set failed to retrieve a state using __call');
    }

    /**
     * @group               modelTestGetForm
     * @group               F0FModel
     * @covers              F0FModel::getForm
     * @covers              F0FModel::onBeforeLoadForm
     * @covers              F0FModel::onAfterLoadForm
     * @dataProvider        getTestGetForm
     * @preventDataLoading
     */
    public function testGetForm($modelinfo, $test, $checks)
    {
        $config['option'] = 'com_foftest';
        $config['name']   = $modelinfo['name'];
        $config['table']  = F0FInflector::singularize($modelinfo['name']);
        $config['input']  = array('option' => 'com_foftest', 'view' => strtolower($modelinfo['name']));

        $methods = array('getState', 'loadForm', 'onBeforeLoadForm', 'onAfterLoadForm');
        $model   = $this->getMock('\\FOF30\\Model\\Model', $methods, array($config));

        // Test vs different form name coming from the request
        $model->expects($this->any())->method('getState')->will($this->returnCallback(
            function($namespace, $default) use ($test)
            {
                if($namespace == 'form_name')
                {
                    return $test['form_name'];
                }
                else
                {
                    return $default;
                }
            }
        ));

        /*
         * WARNING! `WITH` and `WILL` mock methods MUST BE CONCATENATED.
         * These two syntax will return different result:
         *
         *      $mock->with()->will() (works)
         *
         *      $mock->with();        (nope!)
         *      $mock->will();
         *
         * I think it's a phpUnit bug or something, so DON'T TOUCH IT
         */

        // Let's check if the onBeforeLoadForm is called with the correct arguments
        // Do I want to modify incoming data in the onBefore event?
        $model->expects($this->any())->method('onBeforeLoadForm')
              ->with($checks['onBefore']['name'], $checks['onBefore']['source'], $checks['onBefore']['options'])
              ->will($this->returnCallback(
                function(&$name, &$source, &$options) use($test)
                {
                    if(isset($test['onBefore']['modify']))
                    {
                        $name    = $test['onBefore']['name'];
                        $source  = $test['onBefore']['source'];
                        $options = $test['onBefore']['options'];
                    }
                }
        ));

        // Let's check if loadForm is called with the correct arguments
        // Force loadForm to return a form we want (or false on error)
        $model->expects($this->any())->method('loadForm')
              ->with($checks['loadForm']['name'], $checks['loadForm']['source'], $checks['loadForm']['options'])
              ->will($this->returnCallback(
                function() use($test)
                {
                    return $test['loadForm'];
                }
        ));

        // Let's check if onAfterLoadForm is called with the correct arguments
        // Force onAfterLoadForm to manipulate the data
        $model->expects($this->any())->method('onAfterLoadForm')
              ->with($checks['onAfter']['form'], $checks['onAfter']['name'], $checks['onAfter']['source'], $checks['onAfter']['options'])
              ->will($this->returnCallback(
                function(&$form, &$name, &$source, &$options) use ($test)
                {
                    if(isset($test['onAfter']['modify']))
                    {
                        $form = $test['onAfter']['form'];
                    }
                }
        ));

        $model->setInput($config['input']);
        $form = $model->getForm($test['data'], $test['loadData'], $test['source']);

        $this->assertEquals($checks['form'], $form, 'F0FModel::getForm returned the wrong value');
    }

    /**
     * @group               modelTestLoadForm
     * @group               F0FModel
     * @covers              F0FModel::LoadForm
     * @dataProvider        getTestLoadForm
     * @preventDataLoading
     */
    public function testLoadForm($modelinfo, $test, $checks)
    {
        $config['input']  = array('option' => 'com_foftest', 'view' => strtolower($modelinfo['name']));

        $methods = array('findFormFilename', 'loadFormData', 'onBeforePreprocessForm', 'preprocessForm', 'onAfterPreprocessForm');
        $model   = $this->getMock('\\FOF30\\Model\\Model', $methods, array($config));

        $model->expects($this->any())->method('findFormFilename')->will($this->returnValue($test['formPath']));
        $model->expects($this->any())->method('loadFormData')->will($this->returnValue($test['data']));

        $formMock = $this->getMock('\\FOF30\\Form\\Form', array('bind'), array('dummy'));
        $formMock->expects($this->any())->method('bind')
                 ->with($checks['bind']['data']);

        $fofform = new ReflectionProperty('\\FOF30\\Form\\Form', 'forms');
        $fofform->setAccessible(true);
        $fofform->setValue('\\FOF30\\Form\\Form', array($test['name'] => $formMock));

        // Let's check if the onBeforePreprocessForm is called with the correct arguments
        // Do I want to modify incoming data in the onBefore event?
        $model->expects($this->any())->method('onBeforePreprocessForm')
              ->with($formMock, $checks['onBefore']['data'])
              ->will($this->returnCallback(
                function(&$form, &$data) use($test)
                {
                    if(isset($test['onBefore']['modify']))
                    {
                        $form  = $test['onBefore']['form']($form);
                        $data  = $test['onBefore']['data'];
                    }
                }
        ));

        // Let's check if the preprocessForm is called with the correct arguments
        // Do I want to modify incoming data in the onPre event?
        $model->expects($this->any())->method('preprocessForm')
            ->with($formMock, $checks['onPre']['data'])
            ->will($this->returnCallback(
                function(&$form, &$data) use($test)
                {
                    if(isset($test['onPre']['modify']))
                    {
                        $form  = $test['onPre']['form']($form);
                        $data  = $test['onPre']['data'];
                    }
                }
        ));

        // Let's check if the onAfterPreprocessForm is called with the correct arguments
        // Do I want to modify incoming data in the onPre event?
        $model->expects($this->any())->method('onAfterPreprocessForm')
            ->with($formMock, $checks['onAfter']['data'])
            ->will($this->returnCallback(
                function(&$form, &$data) use($test)
                {
                    if(isset($test['onAfter']['modify']))
                    {
                        $form  = $test['onAfter']['form']($form);
                        $data  = $test['onAfter']['data'];
                    }
                }
        ));

        $method = new ReflectionMethod($model, 'loadForm');
        $method->setAccessible(true);

        $form = $method->invoke($model, $test['name'], $test['source'], $test['options'], $test['clear'], $test['xpath']);

        if(isset($checks['errMsg']))
        {
            $this->assertEquals($checks['errMsg'], $model->getError(), 'F0FModel::loadForm failed to set the correct message while an exception is thrown');
        }

        $this->assertEquals($checks['form'], (bool)$form, 'F0FModel::loadForm returned a wrong value');
    }

    /**
     * @group               modelTestFindFormFilename
     * @group               F0FModel
     * @covers              F0FModel::findFormFilename
     * @dataProvider        getTestFindFormFilename
     * @preventDataLoading
     */
    public function testFindFormFilename($modelinfo, $test, $checks)
    {
        $config['input']  = array('option' => 'com_foftest', 'view' => strtolower($modelinfo['name']));

        $model = F0FModel::getTmpInstance($modelinfo['name'], 'FoftestModel', $config);
        $model->setInput($config['input']);

        // First of all I stub the filesystem object, so it won't strip out the protocol part
        $filesystem = $this->getMock('\\FOF30\\Integration\\Joomla\\Platform', array('fileExists'));
        $filesystem->expects($this->any())
                   ->method('fileExists')
                   ->will($this->returnCallback(function($file){ return is_file($file);}));

        $methods = array('getTemplateOverridePath');

        if(isset($test['suffix']))
        {
            $methods[] = 'getTemplateSuffixes';
        }

        $platform = $this->getMock('\\FOF30\\Integration\\Joomla\\Platform', $methods);

        // Then I have to trick the platform, providing a template path
        $platform->expects($this->any())
                 ->method('getTemplateOverridePath')
                 ->will($this->returnValue(JPATH_ROOT.'/administrator/templates/system'));

        // Finally, force the platform to return my mocked object
        $platform->setFilesystemObject($filesystem);

        // Do I want to mock the suffix returned, too?
        if(isset($test['suffix']))
        {
            // I can mock it, so I won't have to update the test with different version of Joomla
            $platform->expects($this->any())->method('getTemplateSuffixes')->will($this->returnValue($test['suffix']));
        }

        F0FPlatform::forceInstance($platform);

        $paths = array();

        foreach($test['paths'] as $path)
        {
            $parts = explode('/', $path);
            $last = array_pop($parts);

            if(strpos($last, '.') === false)
            {
                $parts[] = $last;
            }

            $paths[] = vfsStream::url('root/'.implode('/', $parts));
        }

        vfsStream::setup('root', null, $test['structure']);

        // I always have to supply paths, since I have to use the filesystem wrapper
        $form = $model->findFormFilename($test['form_name'], $paths);

        $this->assertEquals($checks['form'], $form, 'F0FModel::findFormFilename returned a wrong value');
    }

    /**
     * @group               modelTestLoadFormData
     * @group               F0FModel
     * @covers              F0FModel::loadFormData
     * @dataProvider        getTestLoadFormData
     * @preventDataLoading
     */
    public function testLoadFormData($test, $checks)
    {
        $config['input']  = array('option' => 'com_foftest', 'view' => 'foobars');

        $model = F0FModel::getTmpInstance('Foobars', 'FoftestModel', $config);

        $property = new ReflectionProperty($model, '_formData');
        $property->setAccessible(true);
        $property->setValue($model, $test['data']);

        $method = new ReflectionMethod($model, 'loadFormData');
        $method->setAccessible(true);

        $data = $method->invoke($model);

        $this->assertEquals($checks['data'], $data, 'F0FModel::loadFormData returned the wrong value');
    }


    /**
     * @group               modelTestPreprocessForm
     * @group               F0FModel
     * @covers              F0FModel::preprocessForm
     * @dataProvider        getTestPreprocessForm
     * @preventDataLoading
     */
    public function testPreprocessForm($test)
    {
        $config['input']  = array('option' => 'com_foftest', 'view' => 'foobars');

        $model = F0FModel::getTmpInstance('Foobars', 'FoftestModel', $config);

        $form = new F0FForm('dummy');

        $platform = $this->getMock('\\FOF30\\Integration\\Joomla\\Platform', array('importPlugin', 'runPlugins'));
        $platform->expects($this->any())->method('importPlugin')->with('content');
        $platform->expects($this->any())->method('runPlugins')
                 ->with('onContentPrepareForm', array($form, array()))
                 ->will($this->returnValue($test['runPlugin']));

        if($test['throwException'])
        {
            $this->setExpectedException('Exception');
        }

        F0FPlatform::forceInstance($platform);

        $method = new ReflectionMethod($model, 'preprocessForm');
        $method->setAccessible(true);
        $method->invokeArgs($model, array(&$form, array()));
    }

    /**
     * @group               modelTestValidateForm
     * @group               F0FModel
     * @covers              F0FModel::validateForm
     * @dataProvider        getTestValidateForm
     * @preventDataLoading
     */
    public function testValidateForm($test, $checks)
    {
        $config['input']  = array('option' => 'com_foftest', 'view' => 'foobars');

        $model = F0FModel::getTmpInstance('Foobars', 'FoftestModel', $config);

        $form = $this->getMock('\\FOF30\\Form\\Form', array('filter', 'validate', 'getErrors'), array('dummy'));
        $form->expects($this->any())->method('filter')
             ->with($checks['data'])
             ->will($this->returnValue($test['filterData']));

        $form->expects($this->any())->method('validate')
             ->with($checks['filterData'], $checks['group'])
             ->will($this->returnValue($test['validate']));

        $form->expects($this->any())->method('getErrors')->will($this->returnValue($test['getErrors']));

        $return = $model->validateForm($form, $test['data'], $test['group']);

        $this->assertEquals($checks['return'], $return, 'F0FModel::validateForm returned a wrong value');

        if(!$checks['return'])
        {
            $this->assertEquals($checks['errMsg'], $model->getError(), 'F0FModel::validateForm set the wrong message when failing');
        }
    }

    public function getTestSetIDsFromRequest()
    {
        return ModelDataprovider::getTestSetIDsFromRequest();
    }

	public function getTestSetId()
	{
		return ModelDataprovider::getTestSetId();
	}

	public function getTestSetIdException()
	{
		return ModelDataprovider::getTestSetIdException();
	}

    public function getTestSetIds()
    {
        return ModelDataprovider::getTestSetIds();
    }

    public function getTestGetItem()
    {
        return ModelDataprovider::getTestGetItem();
    }

    public function getTestBuildQuery()
    {
        return ModelDataprovider::getTestBuildQuery();
    }

    public function getTestGetItemList()
    {
        return ModelDataprovider::getTestGetItemList();
    }

    public function getTestGetIterator()
    {
        return ModelDataprovider::getTestGetIterator();
    }

    public function getTestSave()
    {
        return ModelDataprovider::getTestSave();
    }

    public function getTestSaveSessionWipe()
    {
        return ModelDataprovider::getTestSaveSessionWipe();
    }

    public function getTestCopy()
    {
        return ModelDataprovider::getTestCopy();
    }

    public function getTestDelete()
    {
        return ModelDataprovider::getTestDelete();
    }

    public function getTestPublish()
    {
        return ModelDataprovider::getTestPublish();
    }

    public function getTestCheckout()
    {
        return ModelDataprovider::getTestCheckout();
    }

    public function getTestCheckin()
    {
        return ModelDataprovider::getTestCheckin();
    }

    public function getTestIsCheckedout()
    {
        return ModelDataprovider::getTestIsCheckedOut();
    }

    public function getTestHit()
    {
        return ModelDataprovider::getTestHit();
    }

    public function getTestMove()
    {
        return ModelDataprovider::getTestMove();
    }

    public function getTestReorder()
    {
        return ModelDataprovider::getTestReorder();
    }

    public function getTestgetTotal()
    {
        return ModelDataprovider::getTestgetTotal();
    }

    public function getTestGetHash()
    {
        return ModelDataprovider::getTestGetHash();
    }

    public function getTestGetList()
    {
        return ModelDataprovider::getTestGetList();
    }

    public function getTestGetTable()
    {
        return ModelDataprovider::getTestGetTable();
    }

    public function getTestCreateTable()
    {
        return ModelDataprovider::getTestCreateTable();
    }

    public function getTestGetForm()
    {
        return ModelDataprovider::getTestGetForm();
    }

    public function getTestLoadForm()
    {
        return ModelDataprovider::getTestLoadForm();
    }

    public function getTestFindFormFilename()
    {
        return ModelDataprovider::getTestFindFormFilename();
    }

    public function getTestLoadFormData()
    {
        return ModelDataprovider::getTestLoadFormData();
    }

    public function getTestPreprocessForm()
    {
        return ModelDataprovider::getTestPreprocessForm();
    }

    public function getTestValidateForm()
    {
        return ModelDataprovider::getTestValidateForm();
    }
}