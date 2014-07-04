<?php
/**
 * @package	    FrameworkOnFramework.UnitTest
 * @subpackage  Iterator
 *
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

class FOFDatabaseIteratorTest extends FtestCaseDatabase
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
     * @group   F0FDatabaseIterator
     * @group   iteratorConstruct
     * @covers  F0FDatabaseIterator::__construct
     */
    public function test__construct()
    {
        $db = JFactory::getDbo();

        $iterator = F0FDatabaseIterator::getIterator('Mysql', $db, null, 'FoftestTableFoobar');
        $this->assertInstanceOf('F0FDatabaseIteratorMysql', $iterator, 'F0FDatabaseIterator loaded the wrong class');
    }

    /**
     * @group   F0FDatabaseIterator
     * @group   iteratorConstruct
     * @covers  F0FDatabaseIterator::__construct
     * @expectedException InvalidArgumentException
     */
    public function test__constructException()
    {
        $db = JFactory::getDbo();
        $iterator = F0FDatabaseIterator::getIterator('Mysql', $db, null, 'WrongTable');
    }

    /**
     * @group   F0FDatabaseIterator
     * @group   iteratorNext
     * @covers  F0FDatabaseIterator::next
     */
    public function testNext()
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
                    ->select('title, foftest_foobar_id')
                    ->from('#__foftest_foobars')
                    ->order('foftest_foobar_id DESC');
        $check = $db->setQuery($query)->loadAssocList();

        $db->disconnect();
        $newDb = clone $db;

        $newDb->setQuery($query);
        $cursor = $newDb->execute();

        $iterator = F0FDatabaseIterator::getIterator('Mysql', $cursor, null, 'FoftestTableFoobar');

        $items = array();

        foreach($iterator as $row)
        {
            $items[] = array(
                'title' => $row->title,
                'foftest_foobar_id' => $row->foftest_foobar_id
            );
        }

        $this->assertEquals($check, $items, '');
    }
}
