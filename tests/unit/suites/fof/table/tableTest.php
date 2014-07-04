<?php
/**
 * @package	    FrameworkOnFramework.UnitTest
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once 'tableDataprovider.php';
require_once JPATH_TESTS.'/unit/core/table/table.php';

class F0FTableTest extends FtestCaseDatabase
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

	public function testGetClone()
	{
		$config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'foobar'));
		$table = F0FTable::getAnInstance('Foobar', 'FoftestTable', $config);
		$table->load(1);
		$this->assertEquals('Guinea Pig row', $table->title);

		$clone = $table->getClone();
		$this->assertEquals('Guinea Pig row', $table->title);
		$this->assertEmpty($clone->title);

		$table->load(2);
		$this->assertEquals('Second row', $table->title);
		$this->assertEmpty($clone->title);

		$clone->title = 'Foobar';
		$this->assertNotEquals('Foobar', $table->title);

		$table->load(1);
		$clone->load(2);
		$this->assertNotEquals($clone->title, $table->title);
	}

	/**
	 * @covers              F0FTable::setKnownFields
	 * @group               F0FTable
	 * @preventDataLoading
	 */
	public function testSetKnownFields()
	{
		$config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'foobar'));

		$table 		= F0FTable::getAnInstance('Foobar', 'FoftestTable', $config);

		$knownFields = array(
			'foo',
			'bar',
			'baz',
		);
		$table->setKnownFields($knownFields);

		$this->assertAttributeEquals($knownFields, 'knownFields', $table, 'Known fields set differ from defined list');
	}

	/**
	 * @covers              F0FTable::getKnownFields
	 * @group               F0FTable
	 * @preventDataLoading
	 */
	public function testGetKnownFields()
	{
		$config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'foobar'));

		$table 		= F0FTable::getAnInstance('Foobar', 'FoftestTable', $config);

		$knownFields = array(
			'foo',
			'bar',
			'baz',
		);
		$table->setKnownFields($knownFields);

		$result = $table->getKnownFields();

		$this->assertEquals($knownFields, $result, 'Known fields fetched differ from defined list');
	}

	/**
	 * @covers              F0FTable::addKnownField
	 * @group               F0FTable
	 * @preventDataLoading
	 */
	public function testAddKnownField()
	{
		$config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'foobar'));

		$table 		= F0FTable::getAnInstance('Foobar', 'FoftestTable', $config);

		$table->addKnownField('foo');
		$table->addKnownField('bar');

		$known_fields = $this->readAttribute($table, 'knownFields');

		$this->assertContains('foo', $known_fields, 'Known fields set differ from defined list');
		$this->assertContains('bar', $known_fields, 'Known fields set differ from defined list');
	}

	/**
	 * @covers              F0FTable::removeKnownField
	 * @group               F0FTable
	 * @preventDataLoading
	 */
	public function testRemoveKnownField()
	{
		$config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'foobar'));

		$table 		= F0FTable::getAnInstance('Foobar', 'FoftestTable', $config);

		$table->addKnownField('foo');
		$table->removeKnownField('foo');

		$known_fields = $this->readAttribute($table, 'knownFields');

		$this->assertNotContains('foo', $known_fields, 'Known fields set differ from defined list');
	}

	/**
	 * @covers              F0FTable::load
	 * @group               tableLoad
	 * @group               F0FTable
	 */
	public function testLoad()
    {
        $config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'foobar'));
        $table 		     = F0FTable::getAnInstance('Foobar', 'FoftestTable', $config);

        $reflection = new ReflectionClass($table);
        $property   = $reflection->getProperty('_tableExists');
        $property->setAccessible(true);
        $property->setValue($table, false);

        $this->assertNull($table->load(), 'Load() should return NULL when no table exists');

        $property->setValue($table, true);

        $this->assertNull($table->load(), 'Load() should return NULL when the primary key has no value');

        $rc = $table->load(1);
        $this->assertTrue($rc, 'Successfully load should return TRUE');
        $this->assertEquals('Guinea Pig row', $table->title, 'Load() by primary key failed');

        $table->load(1);
        $table->load('FOOBAR', false);
        $this->assertEquals('Guinea Pig row', $table->title, "Load() by non-existent primary key (without reset) shouldn't touch table fields");

        // Reset everything
        $table->reset();
        $table->foftest_foobar_id = null;

        $table->load(array('slug' => 'guinea-pig-row'));
        $this->assertEquals(1, $table->foftest_foobar_id, 'Load() by fields to match failed');
    }

	/**
	 * @covers              F0FTable::load
	 * @group               tableLoadJoined
	 * @group               F0FTable
	 * @dataProvider        getTestLoadJoined
	 */
	public function testLoadJoined($tableinfo, $test, $check)
	{
		$db = JFactory::getDbo();

		$table = new FtestTable($tableinfo['table'], $tableinfo['id'], $db, $tableinfo['config']);

		foreach($check['columns'] as $column)
		{
			$this->assertObjectHasAttribute($column, $table, sprintf('Joined field %s not set', $column));
		}

		$rc = $table->load($test['cid']);
		$this->assertEquals($check['return'], $rc, 'Load with joined fields: wrong return value');
	}

	/**
	 * @group               tableCheck
	 * @group               F0FTable
	 * @covers              F0FTable::check
	 * @preventDataLoading
	 */
	public function testCheck()
    {
        $config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'foobar'));
        $table 		     = F0FTable::getAnInstance('Foobar', 'FoftestTable', $config);

        $reflection = new ReflectionClass($table);
        $property   = $reflection->getProperty('_autoChecks');
        $property->setAccessible(true);
        $property->setValue($table, false);

        $this->assertTrue($table->check(), 'Check() should return true when autoChecks are disabled');

        $property->setValue($table, true);

        $table->foftest_foobar_id   = 999;
        $table->title               = 'Dummy title';
        $table->slug                = 'dummy-title';
        $table->enabled             = 1;
        $table->ordering            = 99;
        $table->created_by          = 0;
        $table->created_on          = '0000-00-00 00:00:00';
        $table->modified_by         = 0;
        $table->modified_on         = '0000-00-00 00:00:00';
        $table->locked_by           = 0;
        $table->locked_on           = '0000-00-00 00:00:00';

        $this->assertTrue($table->check(), 'Check() should return true when some "magic" field is empty');

        $table->foftest_foobar_id   = 999;
        $table->title               = '';
        $table->slug                = '';
        $table->enabled             = 1;
        $table->ordering            = 99;
        $table->created_by          = 0;
        $table->created_on          = '0000-00-00 00:00:00';
        $table->modified_by         = 0;
        $table->modified_on         = '0000-00-00 00:00:00';
        $table->locked_by           = 0;
        $table->locked_on           = '0000-00-00 00:00:00';

        $this->assertFalse($table->check(), 'Check() should return false when some required field is empty');

        $table->foftest_foobar_id   = 999;
        $table->title               = '';
        $table->slug                = '';
        $table->enabled             = 1;
        $table->ordering            = 99;
        $table->created_by          = 0;
        $table->created_on          = '0000-00-00 00:00:00';
        $table->modified_by         = 0;
        $table->modified_on         = '0000-00-00 00:00:00';
        $table->locked_by           = 0;
        $table->locked_on           = '0000-00-00 00:00:00';

        $table->setSkipChecks(array('title', 'slug'));

        $this->assertTrue($table->check(), 'Check() should return false when some required field is empty');
    }

	/**
	 * @covers              F0FTable::reset
	 * @group               F0FTable
	 */
	public function testReset()
	{
		$db = JFactory::getDbo();
		$methods = array('onBeforeReset', 'onAfterReset');
		$constr_args = array('jos_foftest_foobars', 'foftest_foobar_id', &$db);

		$table = $this->getMock('F0FTable',	$methods, $constr_args,	'',	true, true, true, true);

		$table->expects($this->any())->method('onBeforeReset')->will($this->returnValue(false));
		$table->expects($this->any())->method('onAfterReset') ->will($this->returnValue(true));

		$this->assertFalse($table->reset(), 'Reset should return FALSE when onBeforeReset returns FALSE');

		unset($table);

		// Rebuild the mock to return true on onBeforeReset
		$table = $this->getMock('F0FTable', $methods, $constr_args,	'',	true, true,	true, true);

		$table->expects($this->any())->method('onBeforeReset')->will($this->returnValue(true));
		$table->expects($this->any())->method('onAfterReset') ->will($this->returnValue(true));

		$table->load(1);
		$rc = $table->reset();

		// First of all let's check the return value
		$this->assertTrue($rc, 'Reset should return TRUE when onBeforeReset returns TRUE');

		// Then let's check if reset method worked
		// @TODO we must check for additional fields, like joined columns
		// This test is not 100% correct, we must change it after F0FTable refactoring
		$fields  = $table->getTableFields();
		$success = true;
		foreach($fields as $field => $class)
		{
			// Primary key shouldn't be resetted
			if($field == $table->getKeyName() && !$table->$field)
			{
				$success = false;
				break;
			}
			elseif($field != $table->getKeyName() && $table->$field)
			{
				$success = false;
				break;
			}
		}

		$this->assertTrue($success, 'Reset method failed on resetting table properties');

		unset($table);

		// Rebuild the mock to return true on onBeforeReset AND false on onAfterReset
		$table = $this->getMock('F0FTable', $methods, $constr_args,	'', true, true,	true, true);

		$table->expects($this->any())->method('onBeforeReset')->will($this->returnValue(true));
		$table->expects($this->any())->method('onAfterReset') ->will($this->returnValue(false));

		$table->load(1);

		$this->assertFalse($table->reset(), 'Reset should return FALSE when onAfterReset is FALSE');
	}

    /**
     * @covers              F0FTable::bind
     * @dataProvider        getTestBind
     * @group               F0FTable
     * @preventDataLoading
     */
    public function testBind($onBefore, $returnValue, $toBind, $toSkip, $toCheck)
    {
        $db          = JFactory::getDbo();
        $methods     = array('onBeforeBind');
        $constr_args = array('jos_foftest_foobars', 'foftest_foobar_id', &$db);

        $table = $this->getMock('F0FTable',	$methods, $constr_args,	'',	true, true, true, true);
        $table->expects($this->any())->method('onBeforeBind')->will($this->returnValue($onBefore));

        $rc = $table->bind($toBind, $toSkip);
        $this->assertEquals($returnValue, $rc, 'Bind() Wrong return value');

        // Only if I'm executing the bind function I do more checks
        if($returnValue)
        {
            foreach($toCheck as $check)
            {
                $this->assertEquals($check['value'], $table->$check['field'], $check['msg']);
            }
        }
    }

	/**
	 * @covers              F0FTable::bind
	 * @group               F0FTable
	 * @preventDataLoading
	 */
	public function testBindException()
    {
        $this->setExpectedException('InvalidArgumentException');

        $config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'foobar'));
        $table 		     = F0FTable::getAnInstance('Foobar', 'FoftestTable', $config);
        $table->bind('This is a wrong argument');
    }

    /**
     * @group tempStore
     */
    public function testtemp()
    {
        $config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'foobar'));
        $table 		     = F0FTable::getAnInstance('Foobar', 'FoftestTable', $config);

        $table->setAssetKey('com_foftest.foobar');

        $table->load(4);
        $table->title = 'Temp';
        $table->asset_id = null;

        $table->store();
    }

    /**
     * @group               tableStore
     * @group               F0FTable
     * @covers              F0FTable::store
     * @dataProvider        getTestStore
     */
    public function testStore($events, $tableinfo, $test, $check)
    {
        $db          = JFactory::getDbo();
        $methods     = array('onBeforeStore', 'onAfterStore');
        $constr_args = array($tableinfo['table'], $tableinfo['id'], &$db);

        $table = $this->getMock('F0FTable',	$methods, $constr_args,	'',	true, true, true, true);

        // Mocking these methods will prevent some F0F features (ie slug creation, created_by set up and so on)
        // I think it's ok since we're going to test that features when we'll test these methods,
        // now we only care about the store() method
        $table->expects($this->any())->method('onBeforeStore')->will($this->returnValue($events['before']));
        $table->expects($this->any())->method('onAfterStore')->will($this->returnValue($events['after']));

        if($test['alias'])
        {
            $table->setColumnAlias('asset_id', $test['alias']);
        }

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        // We have to manually provide this info, since we can't use the getInstance method (we have to mock)
        if($test['assetkey'])
        {
            $table->setAssetKey($test['assetkey']);
        }

        $table->bind($test['bind']);

        if($test['nullable'])
        {
            foreach($test['nullable'] as $field => $value)
            {
                $table->$field = null;
            }
        }

        $rc = $table->store($test['updateNulls']);
        $this->assertEquals($check['return'], $rc, 'Store: wrong return value');

        if($check['more'])
        {
            $tocheck = $test['bind'];
            if($test['updateNulls'])
            {
                $tocheck = array_merge($tocheck, $test['nullable']);
            }

            $k = $table->getKeyName();
            $query = $db->getQuery(true)
                        ->select('*')
                        ->from($tableinfo['table'])
                        ->where($tableinfo['id'].' = '.$table->$k);
            $row = $db->setQuery($query)->loadAssoc();

            foreach($tocheck as $field => $value)
            {
                if($test['updateNulls'] && array_key_exists($field, $test['nullable']))
                {
                    $this->assertEmpty($row[$field], sprintf('Store: wrong stored value, %s instead of empty', $row[$field]));
                }
                else
                {
                    $this->assertEquals($row[$field], $value, sprintf('Store: wrong stored value, %s instead of %s', $value, $row[$field]));
                }
            }
        }

    }

	/**
     * @group               tableMove
	 * @group               F0FTable
     * @covers              F0FTable::move
     * @dataProvider        getTestMove
     */
    public function testMove($events, $tableinfo, $test, $check)
    {
        $db          = JFactory::getDbo();
        $methods     = array('onBeforeMove', 'onAfterMove');
        $constr_args = array($tableinfo['table'], $tableinfo['id'], &$db);

        $table = $this->getMock('F0FTable',	$methods, $constr_args,	'',	true, true, true, true);
        $table->expects($this->any())->method('onBeforeMove')->will($this->returnValue($events['before']));
        $table->expects($this->any())->method('onAfterMove')->will($this->returnValue($events['after']));

        if(isset($test['alias']))
        {
            $table->setColumnAlias('ordering', $test['alias']);
        }

        $ordering = $table->getColumnAlias('ordering');

        if($test['id'])
        {
            $table->load($test['id']);
        }

        $rc = $table->move($test['delta'], $test['where']);

        $this->assertEquals($check['return'], $rc, 'Move() wrong return value');

        // Only if I'm executing the move function I do more checks
        if(isset($check['more']) && $check['more'])
        {
            // Is the current record ok?
            $this->assertEquals($check['value'], $table->$ordering, $check['msg']);

            // Let's check that the moved record has the correct ordering
            if(isset($check['find']))
            {
                $table->load($check['find']['id']);
                $this->assertEquals($check['find']['value'], $table->$ordering, $check['find']['msg']);
            }
        }
    }

	/**
	 * @group               tableMove
	 * @group               F0FTable
	 * @covers              F0FTable::move
	 * @preventDataLoading
	 */
	public function testMoveException()
	{
		$this->setExpectedException('UnexpectedValueException');

		$config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'bare'));
		$table 		     = F0FTable::getAnInstance('Bare', 'FoftestTable', $config);
		$table->move(0);
	}

	/**
     * @group               tableReorder
	 * @group               F0FTable
	 * @covers              F0FTable::reorder
     * @dataProvider        getTestReorder
     */
    public function testReorder($events, $tableinfo, $test, $check)
    {
        $db          = JFactory::getDbo();
        $methods     = array('onBeforeReorder', 'onAfterReorder');
        $constr_args = array($tableinfo['table'], $tableinfo['id'], &$db);

        $table = $this->getMock('F0FTable',	$methods, $constr_args,	'',	true, true, true, true);
        $table->expects($this->any())->method('onBeforeReorder')->will($this->returnValue($events['before']));
        $table->expects($this->any())->method('onAfterReorder')->will($this->returnValue($events['after']));

        if(isset($test['alias']))
        {
            $table->setColumnAlias('ordering', $test['alias']);
        }

        if($test['id'] && $test['ordering'])
        {
            $ordering = $table->getColumnAlias('ordering');

	        $table->setAssetKey('com_foftest.foobar');
            $table->load($test['id']);
            $table->$ordering = $test['ordering'];
            $table->store();
        }

        $rc = $table->reorder($test['where']);

        // First of all, let's check the return value
        $this->assertEquals($check['return'], $rc, 'Reorder() wrong return value');

        if(isset($check['more']) && $check['more'])
        {
            // Then, let's check if the reorder method worked
            $query = $db->getQuery(true)
                        ->select(array($tableinfo['id'], $ordering))
                        ->from($tableinfo['table'])
                        ->order($ordering.' ASC');

            if($test['where'])
            {
                $query->where($test['where']);
            }

            $rows = $db->setQuery($query)->loadAssocList();

            $this->assertEquals($check['list'], $rows, $check['msg']);
        }
    }

    /**
     * @group               tableReorder
     * @group               F0FTable
     * @covers              F0FTable::reorder
     * @preventDataLoading
     */
    public function testReorderException()
    {
        $this->setExpectedException('UnexpectedValueException');

        $config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'bare'));
        $table 		     = F0FTable::getAnInstance('Bare', 'FoftestTable', $config);
        $table->reorder();
    }

    /**
     * @group               tableCheckout
     * @group               F0FTable
     * @covers              F0FTable::checkout
     * @dataProvider        getTestCheckout
     */
    public function testCheckout($tableinfo, $test, $check)
    {
        $db    = JFactory::getDbo();
        $table = new F0FTable($tableinfo['table'], $tableinfo['id'], $db);

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        if($test['alias'])
        {
            $table->setColumnAlias('locked_by', $test['alias']['lockby']);
            $table->setColumnAlias('locked_on', $test['alias']['lockon']);
        }

        $rc = $table->checkout($test['user'], $test['id']);

        $this->assertEquals($check['return'], $rc, 'Checkout() Wrong return value');

        if($check['more'])
        {
            // Let's check if everything is alright
            $pk        = $table->getKeyName();
            $locked_by = $table->getColumnAlias('locked_by');
            $locked_on = $table->getColumnAlias('locked_on');

            $query = $db->getQuery(true)
                        ->select(array($locked_by, $locked_on))
                        ->from($table->getTableName())
                        ->where($pk.' = '.($test['loadid'] ? $test['loadid'] : $test['id']));

            $row = $db->setQuery($query)->loadObject();

            $this->assertEquals($table->$locked_by, $row->$locked_by, 'Checkout() Wrong value for '.$locked_by.' property');
            $this->assertEquals($table->$locked_on, $row->$locked_on, 'Checkout() Wrong value for '.$locked_on.' property');
        }
    }

    /**
     * @group               tableCheckin
     * @group               F0FTable
     * @covers              F0FTable::checkin
     * @dataProvider        getTestCheckin
     */
    public function testCheckin($tableinfo, $test, $check)
    {
        $db    = JFactory::getDbo();
        $table = new F0FTable($tableinfo['table'], $tableinfo['id'], $db);

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        if($test['alias'])
        {
            $table->setColumnAlias('locked_by', $test['alias']['lockby']);
            $table->setColumnAlias('locked_on', $test['alias']['lockon']);
        }

        $rc = $table->checkin($test['id']);

        $this->assertEquals($check['return'], $rc, 'Checkin() Wrong return value');

        if($check['more'])
        {
            // Let's check if everything is alright
            $pk        = $table->getKeyName();
            $locked_by = $table->getColumnAlias('locked_by');
            $locked_on = $table->getColumnAlias('locked_on');

            $query = $db->getQuery(true)
                        ->select(array($locked_by, $locked_on))
                        ->from($table->getTableName())
                        ->where($pk.' = '.($test['loadid'] ? $test['loadid'] : $test['id']));

            $row = $db->setQuery($query)->loadObject();

            $this->assertEquals('0', $table->$locked_by, 'Checkin() Wrong table value for '.$locked_by.' property');
            $this->assertEquals('' , $table->$locked_on, 'Checkin() Wrong table value for '.$locked_on.' property');
            $this->assertEquals('0', $row->$locked_by  , 'Checkin() Wrong db value for '.$locked_by.' property');
            $this->assertEquals('0000-00-00 00:00:00', $row->$locked_on, 'Checkin() Wrong db value for '.$locked_on.' property');
        }
    }

    /**
     * In this test we used a trick: since Joomla uses JTableSession calling
     * JTable::getInstance('session'), it's impossible to mock. So we create a fake user
     * that's surfing in our site writing directly into the session table
     *
     * @group               tableIsCheckedOut
     * @group               F0FTable
     * @covers              F0FTable::isCheckedOut
     * @dataProvider        getTestIsCheckedOut
     */
    public function testIsCheckedOut($tableinfo, $test, $check)
    {
        $db    = JFactory::getDbo();
        $table = new F0FTable($tableinfo['table'], $tableinfo['id'], $db);

        if($test['alias'])
        {
            $table->setColumnAlias('locked_by', $test['alias']['lockby']);
            $table->setColumnAlias('locked_on', $test['alias']['lockon']);
        }

        $table->load($test['id']);
        $this->assertEquals($check['return'], $table->isCheckedOut($test['with']), $check['msg']);
    }

	/**
	 * @covers              F0FTable::isCheckedOut
	 * @group               F0FTable
	 * @preventDataLoading
	 */
	public function testIsCheckedOutExcpetion()
    {
        $this->setExpectedException('UnexpectedValueException');

        $config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'bare'));
        $table 		     = F0FTable::getAnInstance('Bare', 'FoftestTable', $config);
        $table->isCheckedOut();
    }

    /**
     * @group               tableCopy
     * @group               F0FTable
     * @covers              F0FTable::copy
     * @dataProvider        getTestCopy
     */
    public function testCopy($events, $tableinfo, $test, $check)
    {
	    // TODO at the moment the case when onAfterCopy returns false is not covered, since
	    // it simply doens't change anything...

        $db          = JFactory::getDbo();
        $methods     = array('onBeforeCopy', 'onAfterCopy');
        $constr_args = array($tableinfo['table'], $tableinfo['id'], &$db);
        $table = $this->getMock('F0FTable',	$methods, $constr_args,	'',	true, true, true, true);
	    $table->expects($this->any())->method('onBeforeCopy')->will($this->returnValue($events['before']));
	    $table->expects($this->any())->method('onAfterCopy')->will($this->returnValue($events['after']));

	    //$table = new F0FTable($tableinfo['table'], $tableinfo['id'], $db);
	    $table->setAssetKey('com_foftest.foobar');

        if($test['alias'])
        {
            foreach($test['alias'] as $field => $alias)
            {
                $table->setColumnAlias($field, $alias);
            }
        }

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        $rc = $table->copy($test['cids']);
        $this->assertEquals($check['return'], $rc, 'Copy: Wrong return value');

        if($check['more'])
        {
	        $nocopy = 0;

	        // Fields that I should ignore while compariring the two rows
			$skipfields[] = $table->getKeyName();
	        $skipfields[] = $table->getColumnAlias('slug');
	        $skipfields[] = $table->getColumnAlias('asset_id');
	        $skipfields[] = $table->getColumnAlias('created_by');
	        $skipfields[] = $table->getColumnAlias('created_on');
	        $skipfields[] = $table->getColumnAlias('modified_by');
	        $skipfields[] = $table->getColumnAlias('modified_on');

	        // Fields that MUST be different between the two rows
	        $difffields[] = $table->getKeyName();
	        $difffields[] = $table->getColumnAlias('slug');
	        $difffields[] = $table->getColumnAlias('asset_id');
	        $difffields[] = $table->getColumnAlias('created_on');

	        // I "cheat" with the id of copied elements to make my life easier, since I already know
	        // the values that they will get... :)
            foreach($check['cids'] as $original => $copy)
            {
	            // The record shouldn't be copied
	            if(!$copy)
	            {
					$nocopy++;
		            continue;
	            }

                $query = $db->getQuery(true)
                            ->select('*')
                            ->from($tableinfo['table'])
                            ->where($tableinfo['id'].' = '.$original);
                $orig_row = $db->setQuery($query)->loadAssoc();

                $query = $db->getQuery(true)
                            ->select('*')
                            ->from($tableinfo['table'])
                            ->where($tableinfo['id'].' = '.$copy);
                $copy_row = $db->setQuery($query)->loadAssoc();

	            // Create two "working" arrays for testing same and diff fields
	            $orig_row_same = $orig_row;
	            $copy_row_same = $copy_row;
	            $orig_row_diff = $orig_row;
	            $copy_row_diff = $copy_row;

	            // Let's remove fields that are different
	            foreach($orig_row as $field => $value)
	            {
		            if(in_array($field, $skipfields))
		            {
			            unset($orig_row_same[$field]);
			            unset($copy_row_same[$field]);
		            }

		            if(!in_array($field, $difffields))
		            {
			            unset($orig_row_diff[$field]);
			            unset($copy_row_diff[$field]);
		            }
	            }

				$this->assertEquals($orig_row_same, $copy_row_same, 'Copy: Non special fields should be the same');
				$this->assertNotEquals($orig_row_diff, $copy_row_diff, "Copy: Special fields shouldn't be the same");
            }

	        // Let's check if
	        if($nocopy)
	        {
		        $query    = $db->getQuery(true)->select('COUNT(*)')->from($tableinfo['table']);
		        $count    = $db->setQuery($query)->loadResult();
		        $expected = 5 + count($check['cids']) - $nocopy;
		        $this->assertEquals($expected, $count, 'Copy: Wrong total number of items, maybe some unwanted records has been copied?');
	        }
        }
    }

	/**
	 * @group               tablePublish
	 * @group               F0FTable
	 * @covers              F0FTable::publish
	 * @dataProvider        getTestPublish
	 */
	public function testPublish($events, $tableinfo, $test, $check)
	{
		$db          = JFactory::getDbo();
		$methods     = array('onBeforePublish');
		$constr_args = array($tableinfo['table'], $tableinfo['id'], &$db);
		$table = $this->getMock('F0FTable',	$methods, $constr_args,	'',	true, true, true, true);
		$table->expects($this->any())->method('onBeforePublish')->will($this->returnValue($events['before']));

		if($test['alias'])
		{
			foreach($test['alias'] as $field => $alias)
			{
				$table->setColumnAlias($field, $alias);
			}
		}

		if($test['loadid'])
		{
			$table->load($test['loadid']);
		}

		$rc = $table->publish($test['cids'], $test['publish'], $test['user']);
		$this->assertEquals($check['return'], $rc, 'Publish: Wrong return value');

		if($check['more'])
		{
			$enabledName = $table->getColumnAlias('enabled');
			$cids        = $test['loadid'] ? (array)$test['loadid'] : (array)$test['cids'];

			// Let's get an indexed array on primary key
			$query = $db->getQuery(true)
						->select(array($table->getKeyName(), $enabledName))
						->from($tableinfo['table'])
						->where($table->getKeyName(). ' IN('.implode(',', $cids).')');

			$rows = $db->setQuery($query)->loadObjectList($table->getKeyName());

			// If there is only record, the publish method should update the table, too
			if(count($check['cids']) == 1 && $test['loadid'])
			{
				$this->assertEquals($test['publish'], $table->$enabledName, 'Publish: wrong value assigned to the table');
			}

			// Is something different from the expected?
			foreach($check['cids'] as $record => $value)
			{
				$this->assertEquals($value, $rows[$record]->$enabledName, 'Publish: record '.$record.' has a wrong publish value in the database');
			}
		}
	}

	/**
	 * @group               tableDelete
	 * @group               F0FTable
	 * @covers              F0FTable::delete
	 * @dataProvider        getTestDelete
	 */
	public function testDelete($events, $tableinfo, $test, $check)
	{
		$db          = JFactory::getDbo();
		$methods     = array_keys($events);

		$id          = max($test['loadid'], $test['cid']);
		$constr_args = array($tableinfo['table'], $tableinfo['id'], &$db);
		$table       = $this->getMock('F0FTable', $methods, $constr_args, '', true, true, true, true);

		foreach($events as $event => $return)
		{
			$table->expects($this->any())->method($event)->will($this->returnValue($return));
		}

		// We have to manually provide this info, since we can't use the getInstance method (we have to mock)
		if(isset($test['assetkey']))
		{
			$table->setAssetKey($test['assetkey']);
		}

		if(isset($test['alias']))
		{
			foreach($test['alias'] as $column => $alias)
			{
				$table->setColumnAlias($column, $alias);
			}
		}

		if($test['loadid'])
		{
			$table->load($test['loadid']);
		}

		$rc = $table->delete($test['cid']);
		$this->assertEquals($check['return'], $rc, 'Delete: Wrong return value');

		if($check['more'])
		{
			$query = $db->getQuery(true)
						->select('COUNT(*)')
						->from($tableinfo['table'])
						->where($table->getKeyName().' = '.$id);
			$count = $db->setQuery($query)->loadResult();

			$this->assertEquals($check['count'], $count, 'Delete: Wrong behavior on record under delete');
		}
	}

	/**
	 * @covers              F0FTable::delete
	 * @group               F0FTable
	 * @preventDataLoading
	 */
	public function testDeleteException()
	{
		$this->setExpectedException('UnexpectedValueException');

		$config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'foobars'));
		$table 		     = F0FTable::getAnInstance('Foobar', 'FoftestTable', $config);
		$table->delete();
	}

	/**
	 * @group               tableHit
	 * @group               F0FTable
	 * @covers              F0FTable::hit
	 * @dataProvider        getTestHit
	 */
	public function testHit($events, $tableinfo, $test, $check)
	{
		$db          = JFactory::getDbo();
		$methods     = array_keys($events);

		$id          = max($test['loadid'], $test['cid']);
		$constr_args = array($tableinfo['table'], $tableinfo['id'], &$db);
		$table       = $this->getMock('F0FTable', $methods, $constr_args, '', true, true, true, true);

		foreach($events as $event => $return)
		{
			$table->expects($this->any())->method($event)->will($this->returnValue($return));
		}

		// We have to manually provide this info, since we can't use the getInstance method (we have to mock)
		if(isset($test['assetkey']))
		{
			$table->setAssetKey($test['assetkey']);
		}

		if(isset($test['alias']))
		{
			foreach($test['alias'] as $column => $alias)
			{
				$table->setColumnAlias($column, $alias);
			}
		}

		if($test['loadid'])
		{
			$table->load($test['loadid']);
		}

		$rc = $table->hit($test['cid']);
		$this->assertEquals($check['return'], $rc, 'F0FTable::hit returned a wrong value');

		if(isset($check['hits']))
		{
			$hitField = $table->getColumnAlias('hits');

			$query = $db->getQuery(true)
						->select($hitField)
						->from($tableinfo['table'])
						->where($tableinfo['id'].' = '.$id);
			$hits = $db->setQuery($query)->loadResult();

			$this->assertEquals($check['hits'], $hits, 'F0FTable::hit saved a wrong value');
			$this->assertEquals($check['hits'], $table->$hitField, 'F0FTable::hit saved a wrong value inside table object');
        }
	}

	/**
	 * @group               tableToCsv
	 * @group               F0FTable
	 * @covers              F0FTable::toCSV
	 * @dataProvider        getTestToCSV
	 */
	public function testToCSV($tableinfo, $test, $check)
	{
		$config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => $tableinfo['table']));
		$table 		     = F0FTable::getAnInstance($tableinfo['table'], 'FoftestTable', $config);

		if($test['loadid'])
		{
			$table->load($test['loadid']);
		}

		$string = $table->toCSV($test['separator']);

		$this->assertEquals($check['string'], $string, 'F0FTable::toCSV returned a wrong value');
	}

	/**
	 * @group               tableGetData
	 * @group               F0FTable
	 * @covers              F0FTable::getData
	 * @dataProvider        getTestGetData
	 */
	public function testGetData($tableinfo, $test, $check)
	{
		$config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => $tableinfo['table']));
		$table 		     = F0FTable::getAnInstance($tableinfo['table'], 'FoftestTable', $config);

		if($test['loadid'])
		{
			$table->load($test['loadid']);
		}

		$return = $table->getData();

		$this->assertEquals($check['return'], $return, 'F0FTable::getData returned a wrong value');
	}

	/**
	 * @group               tableGetCSVHeader
	 * @group               F0FTable
	 * @covers              F0FTable::getCSVHeader
	 * @dataProvider        getCSVHeader
	 * @preventDataLoading
	 */
	public function testGetCSVHeader($tableinfo, $test, $check)
	{
		$config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => $tableinfo['table']));
		$table 		     = F0FTable::getAnInstance($tableinfo['table'], 'FoftestTable', $config);

		if($test['loadid'])
		{
			$table->load($test['loadid']);
		}

		$string = $table->getCSVHeader($test['separator']);

		$this->assertEquals($check['string'], $string, 'F0FTable::getCSVHeader returned a wrong value');
	}

	/**
	 * @group               tableGetTableFields
	 * @group               F0FTable
	 * @covers              F0FTable::getTableFields
	 * @dataProvider        getTableFields
	 * @preventDataLoading
	 */
	public function testGetTableFields($tableinfo, $test, $check)
	{
		$config['input']           = new F0FInput(array('option' => 'com_foftest', 'view' => $tableinfo['table']));
		$config['use_table_cache'] = $test['use_table_cache'];

		$table = F0FTable::getAnInstance($tableinfo['table'], 'FoftestTable', $config);

		if(isset($test['joomlaCache']))
		{
			$mock = $this->getMock('F0FIntegrationJoomlaPlatform', array('getCache'));

			if($test['joomlaCache'])
			{
				$raw   = file_get_contents(JPATH_TESTS.'/unit/core/cache/cache_joomla.txt');
				$cache = unserialize($raw);

                $t = $cache->get('tables');

				$mock->expects($this->any())->method('getCache')->will($this->returnCallback(function($arg) use (&$cache){
					return $cache->get($arg, null);
				}));
			}
			else
			{
				$mock->expects($this->any())->method('getCache')->will($this->returnValue(null));
			}

			F0FPlatform::forceInstance($mock);
		}

		if(isset($test['tableCache']))
		{
			$property = new ReflectionProperty($table, 'tableCache');
			$property->setAccessible(true);
			$property->setValue($table, $test['tableCache']);
		}

		if(isset($test['tableFieldCache']))
		{
			$property = new ReflectionProperty($table, 'tableFieldCache');
			$property->setAccessible(true);
			$property->setValue($table, $test['tableFieldCache']);
		}

		$return = $table->getTableFields(isset($test['table']) ? $test['table'] : null);

		if(is_array($return))
		{
			$fields = array_keys($return);
		}
		else
		{
			$fields = $return;
		}


		$this->assertEquals($check['fields'], $fields, 'F0FTable::getTableFields returned the wrong value');
	}

	/**
	 * @group               tableIsQuoted
	 * @group               F0FTable
	 * @covers              F0FTable::isQuoted
	 * @dataProvider        getIsQuoted
	 * @preventDataLoading
	 */
	public function testIsQuoted($tableinfo, $test, $check)
	{
		$config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => $tableinfo['table']));
		$table 		     = F0FTable::getAnInstance($tableinfo['table'], 'FoftestTable', $config);

		$method = new ReflectionMethod($table, 'isQuoted');
		$method->setAccessible(true);
		$return = $method->invoke($table, $test['column']);

		$this->assertEquals($check['return'], $return, 'F0FTable::isQuoted returned a wrong value');
	}

    /**
     * @group               tableOnBeforeStore
     * @group               F0FTable
     * @covers              F0FTable::onBeforeStore
     * @dataProvider        getTestOnBeforeStore
     */
    public function testOnBeforeStore($tableinfo, $test, $check)
    {
        $config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => $tableinfo['table']));

        if(isset($test['tbl_key']))
        {
            $config['tbl_key'] = $test['tbl_key'];
        }

        $table = F0FTable::getAnInstance($tableinfo['table'], 'FoftestTable', $config);

        // Let's mock the platform in order to fake an user
        $user = (object) array('id' => 42);
        $mock = $this->getMock('F0FIntegrationJoomlaPlatform', array('getUser'));
        $mock->expects($this->any())->method('getUser')->will($this->returnValue($user));
        F0FPlatform::forceInstance($mock);

        if(isset($test['alias']))
        {
            foreach($test['alias'] as $column => $alias)
            {
                $table->setColumnAlias($column, $alias);
            }
        }

        $table->bind($test['bind']);

        $method = new ReflectionMethod($table, 'onBeforeStore');
        $method->setAccessible(true);
        $return = $method->invoke($table, $test['updateNulls']);

        $this->assertEquals($check['return'], $return, 'F0FTable::onBeforeStore return a wrong value');

        $fields = $table->getData();

        // Manual checks on datetimes
        $created_on = $table->getColumnAlias('created_on');

        if(isset($check['fields'][$created_on]) && $check['fields'][$created_on] == 'NOT NULL' )
        {
            $this->assertNotEmpty($fields[$created_on], 'F0FTable::onBeforeStore assigned a wrong value to the "'.$created_on.'" field');
            unset($fields[$created_on]);
            unset($check['fields'][$created_on]);
        }

        $modified_on = $table->getColumnAlias('modified_on');

        if(isset($check['fields'][$modified_on]) && $check['fields'][$modified_on] == 'NOT NULL' )
        {
            $this->assertNotEmpty($fields[$modified_on], 'F0FTable::onBeforeStore assigned a wrong value to the "'.$modified_on.'" field');
            unset($fields[$modified_on]);
            unset($check['fields'][$modified_on]);
        }

        $this->assertEquals($check['fields'], $fields, 'F0FTable::onBeforeStore assigned a wrong value to a "magic" field');
    }

    /**
     * @group               tableGetAssetName
     * @group               F0FTable
     * @covers              F0FTable::getAssetName
     * @dataProvider        getTestGetAssetName
     */
    public function testGetAssetName($tableinfo, $test, $check)
    {
        $config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => $tableinfo['table']));

        if(isset($test['tbl_key']))
        {
            $config['tbl_key'] = $test['tbl_key'];
        }

        $table = F0FTable::getAnInstance($tableinfo['table'], 'FoftestTable', $config);

        if (isset($test['alias']))
        {
            foreach($test['alias'] as $column => $alias)
            {
                $table->setColumnAlias($column, $alias);
            }

            $table->setAssetsTracked(true);
        }

        $table->load($test['loadid']);

        $assetName = $table->getAssetName();

        $this->assertEquals($check['assetName'], $assetName, 'F0FTable::getAssetName return a wrong asset name');
    }

    /**
     * @group               tableGetAssetName
     * @group               F0FTable
     * @covers              F0FTable::getAssetName
     */
    public function testGetAssetNameException()
    {
        $this->setExpectedException('UnexpectedValueException');

        $config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'foobars'));
        $table 		     = F0FTable::getAnInstance('Foobar', 'FoftestTable', $config);

        $table->getAssetName();
    }

	/**
	 * @covers              F0FTable::getContentType
	 * @group               F0FTable
	 * @dataProvider        getTestGetContentType
	 */
	public function testGetContentType($option, $view, $expected, $message)
	{
		$config['input'] = new F0FInput(array('option' => $option, 'view' => $view));

		$table = F0FTable::getAnInstance('Foobar', 'FoftestTable', $config);
		$this->assertEquals($expected, $table->getContentType(), $message);
	}

    /**
     * @covers              F0FTable::getRelations
     * @group               F0FTable
     */
    public function testGetRelations()
    {
        $config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'foobar'));

        $table = F0FTable::getAnInstance('Foobar', 'FoftestTable', $config);
        $relations = $table->getRelations();

        $this->assertInstanceOf('F0FTableRelations', $relations, 'F0FTable::getRelations should return an instance of F0FTableRelations');
    }

	public function getTestLoadJoined()
	{
		return TableDataprovider::getTestLoadJoined();
	}

    public function getTestBind()
    {
		return TableDataprovider::getTestBind();
    }

    public function getTestStore()
    {
	    return TableDataprovider::getTestStore();
    }

    public function getTestMove()
    {
		return TableDataprovider::getTestMove();
    }

    public function getTestReorder()
    {
		return TableDataprovider::getTestReorder();
    }

    public function getTestCheckout()
    {
	    return TableDataprovider::getTestCheckout();
    }

    public function getTestCheckin()
    {
		return TableDataprovider::getTestCheckin();
    }

    public function getTestIsCheckedOut()
    {
	    return TableDataprovider::getTestIsCheckedOut();
    }

	public function getTestCopy()
	{
		return TableDataprovider::getTestCopy();
	}

	public function getTestPublish()
	{
		return TableDataprovider::getTestPublish();
	}

	public function getTestDelete()
	{
		return TableDataprovider::getTestDelete();
	}

	public function getTestHit()
	{
		return TableDataprovider::getTestHit();
	}

	public function getTestToCSV()
	{
		return TableDataprovider::getTestToCSV();
	}

	public function getTestGetData()
	{
		return TableDataprovider::getTestGetData();
	}

	public function getCSVHeader()
	{
		return TableDataprovider::getCSVHeader();
	}

	public function getTableFields()
	{
		return TableDataprovider::getTableFields();
	}

	public function getIsQuoted()
	{
		return TableDataprovider::getIsQuoted();
	}

    public function getTestOnBeforeStore()
    {
        return TableDataprovider::getTestOnBeforeStore();
    }

    public function getTestGetAssetName()
    {
        return TableDataprovider::getTestGetAssetName();
    }

	public function getTestGetContentType()
	{
		return TableDataprovider::getTestGetContentType();
	}
}