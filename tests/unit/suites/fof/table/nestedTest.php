<?php
use \Mockery as m;

/**
 * @package	    FrameworkOnFramework.UnitTest
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once JPATH_TESTS.'/unit/core/table/nested.php';
require_once 'nestedDataprovider.php';

/*
 * In these tests, in order to avoid countless queries, we use hardcoded values to check the results.
 * This means that if you ever change the data for nested sets, you'll have to double check these tests, since most likely they will break
 */
class F0FTableNestedTest extends FtestCaseDatabase
{
    protected function setUp()
    {
        $loadDataset = true;
        $annotations = $this->getAnnotations();

        // Do I need a dataset for this set or not?
        if (isset($annotations['method']) && isset($annotations['method']['preventDataLoading'])) {
            $loadDataset = false;
        }

        parent::setUp($loadDataset);

        F0FPlatform::forceInstance(null);
        F0FTable::forceInstance(null);
    }

    protected function tearDown()
    {
        parent::tearDown();

        m::close();
    }

    /**
     * @group               nested__contruct
     * @group               F0FTableNested
     * @covers              F0FTableNested::__construct
     * @dataProvider        NestedDataprovider::getTest__construct
     * @preventDataLoading
     */
    public function test__construct($test, $check)
    {
        if($check['exception'])
        {
            $this->setExpectedException('RuntimeException');
        }

        $db = JFactory::getDbo();

        new F0FTableNested($test['table'], $test['id'], $db);
    }

    /**
     * @group               nestedTestCheck
     * @group               F0FTableNested
     * @covers              F0FTableNested::check
     * @dataProvider        NestedDataprovider::getTestCheck
     * @preventDataLoading
     */
    public function testCheck($test, $check)
    {
        $db = JFactory::getDbo();

        $table = $this->getMock('F0FTableNested', array('resetTreeCache'), array($test['table'], $test['id'], &$db));
        $table->expects($this->any())->method('resetTreeCache')->willReturn($this->returnValue(null));

        foreach($test['fields'] as $field => $value)
        {
            $table->$field = $value;
        }

        $return = $table->check();

        $this->assertEquals($check['return'], $return, 'F0FTableNested::check returned the wrong value');

        foreach($check['fields'] as $field => $expected)
        {
            if(is_null($expected))
            {
                $this->assertObjectNotHasAttribute($field, $table, 'F0FTableNested::check set the field '.$field.' even if it should not');
            }
            else
            {
                $this->assertEquals($expected, $table->$field, 'F0FTableNested::check failed to set the field '.$field);
            }
        }
    }

    /**
     * @group               nestedTestDelete
     * @group               F0FTableNested
     * @covers              F0FTableNested::delete
     * @dataProvider        NestedDataprovider::getTestDelete
     */
    public function testDelete($test, $check)
    {
        $db = JFactory::getDbo();

        $table = F0FTable::getAnInstance('Nestedset', 'FoftestTable');

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        $return = $table->delete($test['delete'], $test['recursive']);

        $this->assertEquals($check['return'], $return, 'F0FTableNested::delete returned the wrong value');

        $query = $db->getQuery(true)->select($table->getKeyName())->from($table->getTableName());
        $items = $db->setQuery($query)->loadColumn();

        $this->assertEmpty(array_intersect($check['deleted'], $items), 'F0FTableNested::delete failed to delete all the items');
    }

    /**
     * @group               nestedTestReorder
     * @group               F0FTableNested
     * @covers              F0FTableNested::reorder
     * @preventDataLoading
     */
    public function testReorder()
    {
        $this->setExpectedException('RuntimeException');

        $db = JFactory::getDbo();

        $table = new F0FTableNested('#__foftest_nestedsets', 'id', $db);
        $table->reorder();
    }

    /**
     * @group               nestedTestMove
     * @group               F0FTableNested
     * @covers              F0FTableNested::move
     * @preventDataLoading
     */
    public function testMove()
    {
        $this->setExpectedException('RuntimeException');

        $db = JFactory::getDbo();

        $table = new F0FTableNested('#__foftest_nestedsets', 'id', $db);
        $table->move(1);
    }

    /**
     * @group               nestedTestCreate
     * @group               F0FTableNested
     * @covers              F0FTableNested::create
     * @dataProvider        NestedDataprovider::getTestCreate
     */
    public function testCreate($test)
    {
        $db = JFactory::getDbo();

        $matcher = $this->never();

        if(!$test['root'])
        {
            $matcher = $this->once();
        }

        $table = $this->getMock('F0FTableNested', array('insertAsChildOf', 'getParent'), array('#__foftest_nestedsets', 'foftest_nestedset_id', &$db));
        $table->expects($this->once())->method('insertAsChildOf')->will($this->returnValue(null));
        // This is just a little trick, so insertAsChildOf won't complain about the argument passed
        $table->expects($matcher)->method('getParent')->willReturnSelf();

        $table->load($test['loadid']);
        $table->create($test['data']);
    }

    /**
     * @group               nestedTestInsertAsRoot
     * @group               F0FTableNested
     * @covers              F0FTableNested::insertAsRoot
     */
    public function testInsertAsRoot()
    {
        $table = F0FTable::getAnInstance('Nestedset', 'FoftestTable');

        $table->title = 'New root';
        $table->insertAsRoot();

        $this->assertTrue($table->isRoot(), 'F0FTableNested::insertAsRoot failed to create a new root');
    }

    /**
     * @group               nestedTestInsertAsRoot
     * @group               F0FTableNested
     * @covers              F0FTableNested::insertAsRoot
     */
    public function testInsertAsRootException()
    {
        $this->setExpectedException('RuntimeException');

        $table = F0FTable::getAnInstance('Nestedset', 'FoftestTable');

        $table->load(1);
        $table->insertAsRoot();
    }

    /**
     * @group               nestedTestInsertAsFirstChildOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::insertAsFirstChildOf
     * @dataProvider        NestedDataprovider::getTestInsertAsFirstChildOf
     */
    public function testInsertAsFirstChildOf($test)
    {
        /** @var F0FTableNested $table */
        /** @var F0FTableNested $parent */

        $db = JFactory::getDbo();

        $table  = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $parent = $table->getClone();

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        if($test['title'])
        {
            $table->title = $test['title'];
        }

        $parent->load($test['parentid']);
        $parentLft = $parent->lft;
        $parentRgt = $parent->rgt;

        $return = $table->insertAsFirstChildOf($parent);

        $this->assertInstanceOf('F0FTableNested', $return, 'F0FTableNested::insertAsFirstChildOf should return an instance of itself for chaining');

        // Assertions on the objects
        $this->assertNotEquals($test['loadid'], $table->getId(), 'F0FTableNested::insertAsFirstChildOf should always create a new node');

        $this->assertEquals($parentLft, $parent->lft, 'F0FTableNested::insertAsFirstChildOf should not touch the lft value of the parent');
        $this->assertEquals($parentRgt + 2, $parent->rgt, 'F0FTableNested::insertAsFirstChildOf should increase the rgt value by 2');
        $this->assertEquals(1, $table->rgt - $table->lft, 'F0FTableNested::insertAsFirstChildOf should insert the node as leaf');
        $this->assertEquals(1, $table->lft - $parent->lft, 'F0FTableNested::insertAsFirstChildOf should insert the node as first child');

        // Great, the returned objects are ok, what about the ACTUAL data saved inside the db?
        $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__foftest_nestedsets')
                    ->where('foftest_nestedset_id = '.$table->foftest_nestedset_id);
        $nodeDb = $db->setQuery($query)->loadObject();

        $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__foftest_nestedsets')
                    ->where('foftest_nestedset_id = '.$parent->foftest_nestedset_id);
        $parentDb = $db->setQuery($query)->loadObject();

        $this->assertEquals($table->lft, $nodeDb->lft, 'F0FTableNested::insertAsFirstChildOf Children object and database lft values are not the same');
        $this->assertEquals($table->rgt, $nodeDb->rgt, 'F0FTableNested::insertAsFirstChildOf Children object and database rgt values are not the same');
        $this->assertEquals($parent->lft, $parentDb->lft, 'F0FTableNested::insertAsFirstChildOf Parent object and database lft values are not the same');
        $this->assertEquals($parent->rgt, $parentDb->rgt, 'F0FTableNested::insertAsFirstChildOf Parnet object and database rgt values are not the same');
    }

    /**
     * @group               nestedTestInsertAsFirstChildOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::insertAsFirstChildOf
     */
    public function testInsertAsFirstChildOfException()
    {
        $this->setExpectedException('RuntimeException');

        $table  = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $parent = $table->getClone();

        $table->insertAsFirstChildOf($parent);
    }

    /**
     * @group               nestedTestInsertAsLastChildOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::insertAsLastChildOf
     * @dataProvider        NestedDataprovider::getTestInsertAsLastChildOf
     */
    public function testInsertAsLastChildOf($test)
    {
        /** @var F0FTableNested $table */
        /** @var F0FTableNested $parent */

        $db = JFactory::getDbo();

        $table  = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $parent = $table->getClone();

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        if($test['title'])
        {
            $table->title = $test['title'];
        }

        $parent->load($test['parentid']);
        $parentLft = $parent->lft;
        $parentRgt = $parent->rgt;

        $return = $table->insertAsLastChildOf($parent);

        $this->assertInstanceOf('F0FTableNested', $return, 'F0FTableNested::insertAsLastChildOf should return an instance of itself for chaining');

        // Assertions on the objects
        $this->assertNotEquals($test['loadid'], $table->getId(), 'F0FTableNested::insertAsLastChildOf should always create a new node');

        $this->assertEquals($parentLft, $parent->lft, 'F0FTableNested::insertAsLastChildOf should not touch the lft value of the parent');
        $this->assertEquals($parentRgt + 2, $parent->rgt, 'F0FTableNested::insertAsLastChildOf should increase the rgt value by 2');
        $this->assertEquals(1, $table->rgt - $table->lft, 'F0FTableNested::insertAsLastChildOf should insert the node as leaf');
        $this->assertEquals(1, $parent->rgt - $table->rgt, 'F0FTableNested::insertAsLastChildOf should insert the node as last child');

        // Great, the returned objects are ok, what about the ACTUAL data saved inside the db?
        $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__foftest_nestedsets')
                    ->where('foftest_nestedset_id = '.$table->foftest_nestedset_id);
        $nodeDb = $db->setQuery($query)->loadObject();

        $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__foftest_nestedsets')
                    ->where('foftest_nestedset_id = '.$parent->foftest_nestedset_id);
        $parentDb = $db->setQuery($query)->loadObject();

        $this->assertEquals($table->lft, $nodeDb->lft, 'F0FTableNested::insertAsLastChildOf Children object and database lft values are not the same');
        $this->assertEquals($table->rgt, $nodeDb->rgt, 'F0FTableNested::insertAsLastChildOf Children object and database rgt values are not the same');
        $this->assertEquals($parent->lft, $parentDb->lft, 'F0FTableNested::insertAsLastChildOf Parent object and database lft values are not the same');
        $this->assertEquals($parent->rgt, $parentDb->rgt, 'F0FTableNested::insertAsLastChildOf Parent object and database rgt values are not the same');
    }

    /**
     * @group               nestedTestInsertAsLastChildOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::insertAsLastChildOf
     */
    public function testInsertAsLastChildOfException()
    {
        $this->setExpectedException('RuntimeException');

        $table  = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $parent = $table->getClone();

        $table->insertAsLastChildOf($parent);
    }

    /**
     * @group               nestedTestInsertLeftOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::insertLeftOf
     * @dataProvider        NestedDataprovider::getTestInsertLeftOf
     */
    public function testInsertLeftOf($test)
    {
        /** @var F0FTableNested $table */
        /** @var F0FTableNested $sibling */

        $db = JFactory::getDbo();

        $table   = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $sibling = $table->getClone();

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        if($test['title'])
        {
            $table->title = $test['title'];
        }

        $sibling->load($test['siblingid']);
        $siblingLft = $sibling->lft;
        $siblingRgt = $sibling->rgt;

        $return = $table->insertLeftOf($sibling);

        $this->assertInstanceOf('F0FTableNested', $return, 'F0FTableNested::insertLeftOf should return an instance of itself for chaining');

        // Assertions on the objects
        $this->assertNotEquals($test['loadid'], $table->getId(), 'F0FTableNested::insertLeftOf should always create a new node');
        $this->assertEquals($siblingLft + 2, $sibling->lft, 'F0FTableNested::insertLeftOf should increase the lft value by 2');
        $this->assertEquals($siblingRgt + 2, $sibling->rgt, 'F0FTableNested::insertLeftOf should increase the rgt value by 2');
        $this->assertEquals(1, $table->rgt - $table->lft, 'F0FTableNested::insertLeftOf should insert the node as leaf');
        $this->assertEquals(1, $sibling->lft - $table->rgt, 'F0FTableNested::insertLeftOf should insert the node on the left of the sibling');

        // Great, the returned objects are ok, what about the ACTUAL data saved inside the db?
        $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__foftest_nestedsets')
                    ->where('foftest_nestedset_id = '.$table->foftest_nestedset_id);
        $nodeDb = $db->setQuery($query)->loadObject();

        $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__foftest_nestedsets')
                    ->where('foftest_nestedset_id = '.$sibling->foftest_nestedset_id);
        $siblingDb = $db->setQuery($query)->loadObject();

        $this->assertEquals($table->lft, $nodeDb->lft, 'F0FTableNested::insertLeftOf Node object and database lft values are not the same');
        $this->assertEquals($table->rgt, $nodeDb->rgt, 'F0FTableNested::insertLeftOf Node object and database rgt values are not the same');
        $this->assertEquals($sibling->lft, $siblingDb->lft, 'F0FTableNested::insertLeftOf Sibling object and database lft values are not the same');
        $this->assertEquals($sibling->rgt, $siblingDb->rgt, 'F0FTableNested::insertLeftOf Sibling object and database rgt values are not the same');
    }

    /**
     * @group               nestedTestInsertLeftOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::insertLeftOf
     */
    public function testInsertLeftOfException()
    {
        $this->setExpectedException('RuntimeException');

        $table   = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $sibling = $table->getClone();

        $table->insertLeftOf($sibling);
    }

    /**
     * @group               nestedTestInsertRightOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::insertRightOf
     * @dataProvider        NestedDataprovider::getTestInsertRightOf
     */
    public function testInsertRightOf($test)
    {
        /** @var F0FTableNested $table */
        /** @var F0FTableNested $sibling */

        $db = JFactory::getDbo();

        $table   = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $sibling = $table->getClone();

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        if($test['title'])
        {
            $table->title = $test['title'];
        }

        $sibling->load($test['siblingid']);
        $siblingLft = $sibling->lft;
        $siblingRgt = $sibling->rgt;

        $return = $table->insertRightOf($sibling);

        $this->assertInstanceOf('F0FTableNested', $return, 'F0FTableNested::insertRightOf should return an instance of itself for chaining');

        // Assertions on the objects
        $this->assertNotEquals($test['loadid'], $table->getId(), 'F0FTableNested::insertRightOf should always create a new node');
        $this->assertEquals($siblingLft, $sibling->lft, 'F0FTableNested::insertRightOf should not modify the lft value');
        $this->assertEquals($siblingRgt, $sibling->rgt, 'F0FTableNested::insertRightOf should not modify the rgt value');
        $this->assertEquals(1, $table->rgt - $table->lft, 'F0FTableNested::insertRightOf should insert the node as leaf');
        $this->assertEquals(1, $table->lft - $sibling->rgt, 'F0FTableNested::insertRightOf should insert the node on the right of the sibling');

        // Great, the returned objects are ok, what about the ACTUAL data saved inside the db?
        $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__foftest_nestedsets')
                    ->where('foftest_nestedset_id = '.$table->foftest_nestedset_id);
        $nodeDb = $db->setQuery($query)->loadObject();

        $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__foftest_nestedsets')
                    ->where('foftest_nestedset_id = '.$sibling->foftest_nestedset_id);
        $siblingDb = $db->setQuery($query)->loadObject();

        $this->assertEquals($table->lft, $nodeDb->lft, 'F0FTableNested::insertRightOf Node object and database lft values are not the same');
        $this->assertEquals($table->rgt, $nodeDb->rgt, 'F0FTableNested::insertRightOf Node object and database rgt values are not the same');
        $this->assertEquals($sibling->lft, $siblingDb->lft, 'F0FTableNested::insertRightOf Sibling object and database lft values are not the same');
        $this->assertEquals($sibling->rgt, $siblingDb->rgt, 'F0FTableNested::insertRightOf Sibling object and database rgt values are not the same');
    }

    /**
     * @group               nestedTestInsertRightOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::insertRightOf
     */
    public function testInsertRightOfException()
    {
        $this->setExpectedException('RuntimeException');

        $table   = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $sibling = $table->getClone();

        $table->insertRightOf($sibling);
    }

    /**
     * @group               nestedTestMoveLeft
     * @group               F0FTableNested
     * @covers              F0FTableNested::moveLeft
     * @dataProvider        NestedDataprovider::getTestMoveLeft
     */
    public function testMoveLeft($test, $check)
    {
        $db = JFactory::getDbo();

        $table = m::mock('FoftestTableNestedset[moveToLeftOf]', array('#__foftest_nestedsets', 'foftest_nestedset_id', &$db, array('_table_class' => 'FoftestTableNestedset')));
        $table->shouldReceive('moveToLeftOf')
              ->times((int) $check['move'])
              ->with(
                m::on(function($leftSibling) use($check) {
                    return $leftSibling->foftest_nestedset_id == $check['leftSibling'];
              }))
              ->andReturn(true);

        $table->load($test['loadid']);

        $table->moveLeft();
    }

    /**
     * @group               nestedTestMoveLeft
     * @group               F0FTableNested
     * @covers              F0FTableNested::moveLeft
     */
    public function testMoveLeftException()
    {
        $this->setExpectedException('RuntimeException');

        $table   = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $table->moveLeft();
    }

    /**
     * @group               nestedTestMoveRight
     * @group               F0FTableNested
     * @covers              F0FTableNested::moveRight
     * @dataProvider        NestedDataprovider::getTestMoveRight
     */
    public function testMoveRight($test, $check)
    {
        $db = JFactory::getDbo();

        $table = m::mock('FoftestTableNestedset[moveToRightOf]', array('#__foftest_nestedsets', 'foftest_nestedset_id', &$db, array('_table_class' => 'FoftestTableNestedset')));
        $table->shouldReceive('moveToRightOf')
            ->times((int) $check['move'])
            ->with(
                m::on(function($leftSibling) use($check) {
                    return $leftSibling->foftest_nestedset_id == $check['rightSibling'];
                }))
            ->andReturn(true);

        $table->load($test['loadid']);

        $table->moveRight();
    }

    /**
     * @group               nestedTestMoveRight
     * @group               F0FTableNested
     * @covers              F0FTableNested::moveRight
     */
    public function testMoveRightException()
    {
        $this->setExpectedException('RuntimeException');

        $table   = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $table->moveRight();
    }

    /**
     * @group               nestedTestMoveToLeftOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::moveToLeftOf
     * @dataProvider        NestedDataprovider::getTestMoveToLeftOf
     */
    public function testMoveToLeftOf($test, $check)
    {
        /** @var F0FTableNested $table */
        /** @var F0FTableNested $sibling */

        $db = JFactory::getDbo();

        $table   = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $sibling = $table->getClone();

        // Am I request to create a different root?
        if($test['newRoot'])
        {
            $root = $table->getClone();
            $root->title = 'New root';
            $root->insertAsRoot();

            $child = $table->getClone();
            $child->title = 'First child 2nd root';
            $child->insertAsChildOf($root);

            $child->reset();

            $child->title = 'Second child 2nd root';
            $child->insertAsChildOf($root);
        }

        $table->load($test['loadid']);
        $sibling->load($test['siblingid']);

        $return = $table->moveToLeftOf($sibling);

        $this->assertInstanceOf('F0FTableNested', $return, 'F0FTableNested::moveToLeftOf should return an instance of itself for chaining');

        // Assertions on the objects
        $this->assertEquals($check['table']['lft'], $table->lft, 'F0FTableNested::moveToLeftOf failed to assign the correct lft value to the node');
        $this->assertEquals($check['table']['rgt'], $table->rgt, 'F0FTableNested::moveToLeftOf failed to assign the correct rgt value to the node');

        // Great, the returned objects are ok, what about the ACTUAL data saved inside the db?
        $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__foftest_nestedsets')
                    ->where('foftest_nestedset_id = '.$table->foftest_nestedset_id);
        $nodeDb = $db->setQuery($query)->loadObject();

        $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__foftest_nestedsets')
                    ->where('foftest_nestedset_id = '.$sibling->foftest_nestedset_id);
        $siblingDb = $db->setQuery($query)->loadObject();

        $this->assertEquals($table->lft, $nodeDb->lft, 'F0FTableNested::moveToLeftOf Node object and database lft values are not the same');
        $this->assertEquals($table->rgt, $nodeDb->rgt, 'F0FTableNested::moveToLeftOf Node object and database rgt values are not the same');
        $this->assertEquals($check['sibling']['lft'], $siblingDb->lft, 'F0FTableNested::moveToLeftOf Saved the wrong lft value for the sibling');
        $this->assertEquals($check['sibling']['rgt'], $siblingDb->rgt, 'F0FTableNested::moveToLeftOf Saved the wrong rgt value for the sibling');
    }

    /**
     * @group               nestedTestMoveToLeftOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::moveToLeftOf
     * @dataProvider        NestedDataprovider::getTestMoveToLeftOfException
     */
    public function testMoveToLeftOfException($test)
    {
        $this->setExpectedException('RuntimeException');

        $table   = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $sibling = $table->getClone();

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        if($test['siblingid'])
        {
            $sibling->load($test['siblingid']);
        }

        $table->moveToLeftOf($sibling);
    }

    /**
     * @group               nestedTestMoveToRightOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::moveToRightOf
     * @dataProvider        NestedDataprovider::getTestMoveToRightOf
     */
    public function testMoveToRightOf($test, $check)
    {
        /** @var F0FTableNested $table */
        /** @var F0FTableNested $sibling */

        $db = JFactory::getDbo();

        $table   = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $sibling = $table->getClone();

        // Am I request to create a different root?
        if($test['newRoot'])
        {
            $root = $table->getClone();
            $root->title = 'New root';
            $root->insertAsRoot();

            $child = $table->getClone();
            $child->title = 'First child 2nd root';
            $child->insertAsChildOf($root);

            $child->reset();

            $child->title = 'Second child 2nd root';
            $child->insertAsChildOf($root);
        }

        $table->load($test['loadid']);
        $sibling->load($test['siblingid']);

        $return = $table->moveToRightOf($sibling);

        $this->assertInstanceOf('F0FTableNested', $return, 'F0FTableNested::moveToRightOf should return an instance of itself for chaining');

        // Assertions on the objects
        $this->assertEquals($check['table']['lft'], $table->lft, 'F0FTableNested::moveToRightOf failed to assign the correct lft value to the node');
        $this->assertEquals($check['table']['rgt'], $table->rgt, 'F0FTableNested::moveToRightOf failed to assign the correct rgt value to the node');

        // Great, the returned objects are ok, what about the ACTUAL data saved inside the db?
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__foftest_nestedsets')
            ->where('foftest_nestedset_id = '.$table->foftest_nestedset_id);
        $nodeDb = $db->setQuery($query)->loadObject();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__foftest_nestedsets')
            ->where('foftest_nestedset_id = '.$sibling->foftest_nestedset_id);
        $siblingDb = $db->setQuery($query)->loadObject();

        $this->assertEquals($table->lft, $nodeDb->lft, 'F0FTableNested::moveToRightOf Node object and database lft values are not the same');
        $this->assertEquals($table->rgt, $nodeDb->rgt, 'F0FTableNested::moveToRightOf Node object and database rgt values are not the same');
        $this->assertEquals($check['sibling']['lft'], $siblingDb->lft, 'F0FTableNested::moveToRightOf Saved the wrong lft value for the sibling');
        $this->assertEquals($check['sibling']['rgt'], $siblingDb->rgt, 'F0FTableNested::moveToRightOf Saved the wrong rgt value for the sibling');
    }

    /**
     * @group               nestedTestMoveToRightOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::moveToRightOf
     * @dataProvider        NestedDataprovider::getTestMoveToRightOfException
     */
    public function testMoveToRightOfException($test)
    {
        $this->setExpectedException('RuntimeException');

        $table   = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $sibling = $table->getClone();

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        if($test['siblingid'])
        {
            $sibling->load($test['siblingid']);
        }

        $table->moveToRightOf($sibling);
    }

    /**
     * @group               nestedTestMakeFirstChildOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::makeFirstChildOf
     * @dataProvider        NestedDataprovider::getTestMakeFirstChildOf
     */
    public function testMakeFirstChildOf($test, $check)
    {
        /** @var F0FTableNested $table */
        /** @var F0FTableNested $parent */

        $db = JFactory::getDbo();

        $table  = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $parent = $table->getClone();

        $table->load($test['loadid']);
        $parent->load($test['parentid']);

        $return = $table->makeFirstChildOf($parent);

        $this->assertInstanceOf('F0FTableNested', $return, 'F0FTableNested::makeFirstChildOf should return an instance of itself for chaining');

        // Assertions on the objects
        $this->assertEquals($check['table']['lft'], $table->lft, 'F0FTableNested::makeFirstChildOf failed to assign the correct lft value to the node');
        $this->assertEquals($check['table']['rgt'], $table->rgt, 'F0FTableNested::makeFirstChildOf failed to assign the correct rgt value to the node');

        // Great, the returned objects are ok, what about the ACTUAL data saved inside the db?
        $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__foftest_nestedsets')
                    ->where('foftest_nestedset_id = '.$table->foftest_nestedset_id);
        $nodeDb = $db->setQuery($query)->loadObject();

        $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__foftest_nestedsets')
                    ->where('foftest_nestedset_id = '.$parent->foftest_nestedset_id);
        $parentDb = $db->setQuery($query)->loadObject();

        $this->assertEquals($table->lft, $nodeDb->lft, 'F0FTableNested::makeFirstChildOf Node object and database lft values are not the same');
        $this->assertEquals($table->rgt, $nodeDb->rgt, 'F0FTableNested::makeFirstChildOf Node object and database rgt values are not the same');
        $this->assertEquals($check['parent']['lft'], $parentDb->lft, 'F0FTableNested::makeFirstChildOf Saved the wrong lft value for the parent');
        $this->assertEquals($check['parent']['rgt'], $parentDb->rgt, 'F0FTableNested::makeFirstChildOf Saved the wrong rgt value for the parent');
    }

    /**
     * @group               nestedTestMakeFirstChildOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::makeFirstChildOf
     * @dataProvider        NestedDataprovider::getTestMakeFirstChildOfException
     */
    public function testMakeFirstChildOfException($test)
    {
        $this->setExpectedException('RuntimeException');

        $table  = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $parent = $table->getClone();

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        if($test['siblingid'])
        {
            $parent->load($test['parentid']);
        }

        $table->makeFirstChildOf($parent);
    }

    /**
     * @group               nestedTestMakeLastChildOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::makeLastChildOf
     * @dataProvider        NestedDataprovider::getTestMakeLastChildOf
     */
    public function testMakeLastChildOf($test, $check)
    {
        /** @var F0FTableNested $table */
        /** @var F0FTableNested $parent */

        $db = JFactory::getDbo();

        $table  = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $parent = $table->getClone();

        $table->load($test['loadid']);
        $parent->load($test['parentid']);

        $return = $table->makeLastChildOf($parent);

        $this->assertInstanceOf('F0FTableNested', $return, 'F0FTableNested::makeLastChildOf should return an instance of itself for chaining');

        // Assertions on the objects
        $this->assertEquals($check['table']['lft'], $table->lft, 'F0FTableNested::makeLastChildOf failed to assign the correct lft value to the node');
        $this->assertEquals($check['table']['rgt'], $table->rgt, 'F0FTableNested::makeLastChildOf failed to assign the correct rgt value to the node');

        // Great, the returned objects are ok, what about the ACTUAL data saved inside the db?
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__foftest_nestedsets')
            ->where('foftest_nestedset_id = '.$table->foftest_nestedset_id);
        $nodeDb = $db->setQuery($query)->loadObject();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__foftest_nestedsets')
            ->where('foftest_nestedset_id = '.$parent->foftest_nestedset_id);
        $parentDb = $db->setQuery($query)->loadObject();

        $this->assertEquals($table->lft, $nodeDb->lft, 'F0FTableNested::makeLastChildOf Node object and database lft values are not the same');
        $this->assertEquals($table->rgt, $nodeDb->rgt, 'F0FTableNested::makeLastChildOf Node object and database rgt values are not the same');
        $this->assertEquals($check['parent']['lft'], $parentDb->lft, 'F0FTableNested::makeLastChildOf Saved the wrong lft value for the parent');
        $this->assertEquals($check['parent']['rgt'], $parentDb->rgt, 'F0FTableNested::makeLastChildOf Saved the wrong rgt value for the parent');
    }

    /**
     * @group               nestedTestMakeLastChildOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::makeLastChildOf
     * @dataProvider        NestedDataprovider::getTestMakeLastChildOfException
     */
    public function testMakeLastChildOfException($test)
    {
        $this->setExpectedException('RuntimeException');

        $table  = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $parent = $table->getClone();

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        if($test['siblingid'])
        {
            $parent->load($test['parentid']);
        }

        $table->makeLastChildOf($parent);
    }

    /**
     * @group               nestedTestMakeRoot
     * @group               F0FTableNested
     * @covers              F0FTableNested::makeRoot
     * @dataProvider        NestedDataprovider::getTestMakeRoot
     */
    public function testMakeRoot($test, $check)
    {
        $db = JFactory::getDbo();

        $table = m::mock('FoftestTableNestedset[moveToRightOf,isRoot,getRoot,equals]',
                            array('#__foftest_nestedsets', 'foftest_nestedset_id', &$db, array('_table_class' => 'FoftestTableNestedset'))
        );

        $table->shouldReceive('moveToRightOf')
            ->times((int) $check['move'])
            ->andReturn(true);

        $table->shouldReceive('isRoot')->andReturn($test['mock']['isRoot']);
        $table->shouldReceive('getRoot')->andReturn($table);
        $table->shouldReceive('equals')->andReturn($test['mock']['equals']);

        $return = $table->makeRoot();

        $this->assertInstanceOf('F0FTableNested', $return, 'F0FTableNested::makeRoot should return an instance of itself for chaining');
    }

    /**
     * @group               nestedTestGetLevel
     * @group               F0FTableNested
     * @covers              F0FTableNested::getLevel
     * @dataProvider        NestedDataprovider::getTestGetLevel
     */
    public function testGetLevel($test, $check)
    {
        $table   = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $table->load($test['loadid']);

        if($test['cache'])
        {
            TestReflection::setValue($table, 'treeDepth', $test['cache']);
        }

        $level = $table->getLevel();

        $this->assertEquals($check['level'], $level, 'F0FTableNested::getLevel returned the wrong level');
    }

    /**
     * @group               nestedTestGetLevel
     * @group               F0FTableNested
     * @covers              F0FTableNested::getLevel
     */
    public function testGetLevelException()
    {
        $this->setExpectedException('RuntimeException');

        $table   = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $table->getLevel();
    }

    /**
     * @group               nestedTestGetParent
     * @group               F0FTableNested
     * @covers              F0FTableNested::getParent
     * @dataProvider        NestedDataprovider::getTestGetParent
     */
    public function testGetParent($test, $check)
    {
        $table   = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $table->load($test['loadid']);

        if(!is_null($test['cache']))
        {
            if($test['cache'] == 'loadself')
            {
                TestReflection::setValue($table, 'treeParent', $table);
            }
            else
            {
                TestReflection::setValue($table, 'treeParent', $test['cache']);
            }
        }

        $parent = $table->getParent();

        $this->assertInstanceOf('F0FTableNested', $parent, 'F0FTableNested::getParent should return an instance of F0FTableNested');
        $this->assertEquals($check['parent'], $parent->foftest_nestedset_id, 'F0FTableNested::getParent returned the wrong parent id');

    }

    /**
     * @group               nestedTestIsRoot
     * @group               F0FTableNested
     * @covers              F0FTableNested::isRoot
     * @dataProvider        NestedDataprovider::getTestIsRoot
     */
    public function testIsRoot($test, $check)
    {
        $db = JFactory::getDbo();

        $table = m::mock('FoftestTableNestedset[getLevel]',
            array('#__foftest_nestedsets', 'foftest_nestedset_id', &$db, array('_table_class' => 'FoftestTableNestedset'))
        );

        $table->shouldReceive('getLevel')
            ->times((int) $check['getLevel'])
            ->andReturn($test['mock']['getLevel']);

        $table->load($test['loadid']);

        $result = $table->isRoot();

        $this->assertEquals($check['result'], $result, 'F0FTableNested::isRoot returned the wrong value');
    }

    /**
     * @group               nestedTestIsLeaf
     * @group               F0FTableNested
     * @covers              F0FTableNested::isLeaf
     * @dataProvider        NestedDataprovider::getTestIsLeaf
     * @preventDataLoading
     */
    public function testIsLeaf($test, $check)
    {
        $table   = F0FTable::getAnInstance('Nestedset', 'FoftestTable');

        $table->lft = $test['lft'];
        $table->rgt = $test['rgt'];

        $result = $table->isLeaf();

        $this->assertEquals($check['result'], $result, 'F0FTableNested::isLeaf ');
    }

    /**
     * @group               nestedTestIsLeaf
     * @group               F0FTableNested
     * @covers              F0FTableNested::isLeaf
     * @preventDataLoading
     */
    public function testIsLeafException()
    {
        $this->setExpectedException('RuntimeException');

        $table   = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $table->isLeaf();
    }

    /**
     * @group               nestedTestIsDescendantOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::isDescendantOf
     * @dataProvider        NestedDataprovider::getTestIsDescendantOf
     */
    public function testIsDescendantOf($test, $check)
    {
        $table  = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $other  = $table->getClone();

        $table->load($test['loadid']);
        $other->load($test['otherid']);

        $result = $table->isDescendantOf($other);

        $this->assertEquals($check['result'], $result, 'F0FTableNested::isDescendantOf returned the wrong value');
    }

    /**
     * @group               nestedTestIsDescendantOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::isDescendantOf
     * @dataProvider        NestedDataprovider::getTestIsDescendantOfException
     */
    public function testIsDescendantOfException($test)
    {
        $this->setExpectedException('RuntimeException');

        $table  = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $other  = $table->getClone();

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        if($test['otherid'])
        {
            $other->load($test['otherid']);
        }

        $table->isDescendantOf($other);
    }

    /**
     * @group               nestedTestIsSelfOrDescendantOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::isSelfOrDescendantOf
     * @dataProvider        NestedDataprovider::getTestIsSelfOrDescendantOf
     */
    public function testIsSelfOrDescendantOf($test, $check)
    {
        $table  = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $other  = $table->getClone();

        $table->load($test['loadid']);
        $other->load($test['otherid']);

        $result = $table->isSelfOrDescendantOf($other);

        $this->assertEquals($check['result'], $result, 'F0FTableNested::isDescendantOf returned the wrong value');
    }

    /**
     * @group               nestedTestIsSelfOrDescendantOf
     * @group               F0FTableNested
     * @covers              F0FTableNested::isSelfOrDescendantOf
     * @dataProvider        NestedDataprovider::getTestIsSelfOrDescendantOfException
     */
    public function testIsSelfOrDescendantOfException($test)
    {
        $this->setExpectedException('RuntimeException');

        $table  = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $other  = $table->getClone();

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        if($test['otherid'])
        {
            $other->load($test['otherid']);
        }

        $table->isSelfOrDescendantOf($other);
    }

    /**
     * @group               nestedTestEquals
     * @group               F0FTableNested
     * @covers              F0FTableNested::equals
     * @dataProvider        NestedDataprovider::getTestEquals
     */
    public function testEquals($test, $check)
    {
        $table  = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $other  = $table->getClone();

        $table->load($test['loadid']);
        $other->load($test['otherid']);

        if(!is_null($test['forceTableId']))
        {
            $pk = $table->getKeyName();
            $table->$pk = $test['forceTableId'];
        }

        if(!is_null($test['forceOtherId']))
        {
            $pk = $other->getKeyName();
            $other->$pk = $test['forceOtherId'];
        }

        $result = $table->equals($other);

        $this->assertEquals($check['result'], $result, 'F0FTableNested::equals returned the wrong value');
    }

    /**
     * @group               nestedTestEquals
     * @group               F0FTableNested
     * @covers              F0FTableNested::equals
     * @dataProvider        NestedDataprovider::getTestEqualsException
     */
    public function testEqualsException($test)
    {
        $this->setExpectedException('RuntimeException');

        $table  = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $other  = $table->getClone();

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        if($test['otherid'])
        {
            $other->load($test['otherid']);
        }

        $table->equals($other);
    }

    /**
     * @group               nestedTestInSameScope
     * @group               F0FTableNested
     * @covers              F0FTableNested::inSameScope
     * @dataProvider        NestedDataprovider::getTestInSameScope
     * @preventDataLoading
     */
    public function testInSameScope($test, $check)
    {
        $db = JFactory::getDbo();

        $table = m::mock('FoftestTableNestedset[isLeaf,isRoot,isChild]',
            array('#__foftest_nestedsets', 'foftest_nestedset_id', &$db, array('_table_class' => 'FoftestTableNestedset'))
        );

        $table->shouldReceive('isLeaf')->andReturn($test['mock']['table']['isLeaf']);
        $table->shouldReceive('isRoot')->andReturn($test['mock']['table']['isRoot']);
        $table->shouldReceive('isChild')->andReturn($test['mock']['table']['isChild']);

        $other = m::mock('FoftestTableNestedset[isLeaf,isRoot,isChild]',
            array('#__foftest_nestedsets', 'foftest_nestedset_id', &$db, array('_table_class' => 'FoftestTableNestedset'))
        );

        $other->shouldReceive('isLeaf')->andReturn($test['mock']['other']['isLeaf']);
        $other->shouldReceive('isRoot')->andReturn($test['mock']['other']['isRoot']);
        $other->shouldReceive('isChild')->andReturn($test['mock']['other']['isChild']);

        $result = $table->inSameScope($other);

        $this->assertEquals($check['result'], $result, 'F0FTableNested::inSameScope returned the wrong value');
    }

    /**
     * @group               nestedTestScopeImmediateDescendants
     * @group               F0FTableNested
     * @covers              F0FTableNested::scopeImmediateDescendants
     * @dataProvider        NestedDataprovider::getTestScopeImmediateDescendants
     */
    public function testScopeImmediateDescendants($test, $check)
    {
        $table = F0FTable::getAnInstance('Nestedset', 'FoftestTable');
        $table->load($test['loadid']);

        TestReflection::invoke($table, 'scopeImmediateDescendants');

        // Let's get the built where clause and "normalize" it
        $where = array_pop(TestReflection::getValue($table, 'whereClauses'));
        preg_match_all('#IN\s?\((.*?)\)#', $where, $matches);

        $where = explode(',', str_replace("'", '', $matches[1][0]));
        $where = array_map('trim', $where);

        $this->assertEquals($check['result'], $where, 'F0FTableNested::scopeImmediateDescendants applied the wrong where');
    }

    /**
     * @group               nestedTestScopeImmediateDescendants
     * @group               F0FTableNested
     * @covers              F0FTableNested::scopeImmediateDescendants
     */
    public function testScopeImmediateDescendantsException()
    {
        $this->setExpectedException('RuntimeException');

        $table = F0FTable::getAnInstance('Nestedset', 'FoftestTable');

        TestReflection::invoke($table, 'scopeImmediateDescendants');
    }

    /**
     * @group               nestedTestGetRoot
     * @group               F0FTableNested
     * @covers              F0FTableNested::getRoot
     * @dataProvider        NestedDataprovider::getTestRoot
     */
    public function testGetRoot($test, $check)
    {
        $table = F0FTable::getAnInstance('Nestedset', 'FoftestTable');

        // Am I request to create a different root?
        if($test['newRoot'])
        {
            $root = $table->getClone();
            $root->title = 'New root';
            $root->insertAsRoot();

            $child = $table->getClone();
            $child->title = 'First child 2nd root';
            $child->insertAsChildOf($root);

            $child->reset();

            $child->title = 'Second child 2nd root';
            $child->insertAsChildOf($root);

            $grandson = $child->getClone();
            $grandson->reset();
            $grandson->title = 'First grandson of second child';
            $grandson->insertAsChildOf($child);

        }

        $table->load($test['loadid']);

        if(!is_null($test['cache']))
        {
            if($test['cache'] == 'loadself')
            {
                TestReflection::setValue($table, 'treeRoot', $table);
            }
            else
            {
                TestReflection::setValue($table, 'treeRoot', $test['cache']);
            }
        }

        $return = $table->getRoot();
        $root   = $return->getId();

        $this->assertEquals($check['result'], $root, 'F0FTableNested::getRoot returned the wrong root');
    }

    /**
     * @group               nestedTestGetRoot
     * @group               F0FTableNested
     * @covers              F0FTableNested::getRoot
     * @dataProvider        NestedDataprovider::getTestRootException
     */
    public function testGetRootException($test)
    {
        $this->setExpectedException('RuntimeException');

        $counter = 0;
        $db      = JFactory::getDbo();

        $table = m::mock('FoftestTableNestedset[get,isRoot]',
            array('#__foftest_nestedsets', 'foftest_nestedset_id', &$db, array('_table_class' => 'FoftestTableNestedset'))
        );

        $table->shouldReceive('isRoot')->andReturn(false);

        // I want to throw an exception at the first run
        if($test['mock']['current'][0])
        {
            $table->shouldReceive('get')->andThrow(new RuntimeException());
        }
        // The first run is ok, the exception will be thrown at the second call
        else
        {
            // That's "funny": since we are cloning the table object, mockery will reset the invocation counter
            // This means that I have to manually track down the invocations and act accordingly
            $table->shouldReceive('get')->andReturn(
                new FOFClosure(array(
                    'current' => function() use($table, &$counter){
                        if(!$counter)
                        {
                            $counter++;

                            $clone = $table->getClone();
                            $clone->lft = 1000;
                            $clone->rgt = 1001;

                            return $clone;
                        }
                        else
                        {
                            throw new RuntimeException();
                        }
                    }
                ))
            );
        }

        if($test['loadid'])
        {
            $table->load($test['loadid']);
        }

        if($test['wrongNode'])
        {
            $table->lft = 2000;
            $table->rgt = 2001;
        }

        $table->getRoot();
    }

    /**
     * @group               nestedTestGetNestedList
     * @group               F0FTableNested
     * @covers              F0FTableNested::getNestedList
     * @dataProvider        NestedDataprovider::getTestGetNestedList
     */
    public function testGetNestedList($test, $check)
    {
        $table = F0FTable::getAnInstance('Nestedset', 'FoftestTable');

        $result = $table->getNestedList($test['column'], $test['key'], $test['separator']);

        $this->assertEquals($check['result'], $result, 'F0FTableNested::getNestedList returned the wrong list');
    }
}
