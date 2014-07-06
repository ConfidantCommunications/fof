<?php

use org\bovigo\vfs\vfsStream;

abstract class ModelDataprovider
{
	public static function getTestSetIDsFromRequest()
    {
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'id'  => 5,
                'cid' => array()
            ),
            array(
                'id'      => 5,
                'id_list' => array(5)
            )
        );

        $data[] = array(
            array('name' => 'foobars'),
            array(
                'id'  => 0,
                'cid' => array(5, 2)
            ),
            array(
                'id'      => 5,
                'id_list' => array(5, 2)
            )
        );

        // cid vs id => cid wins
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'id'  => 4,
                'cid' => array(2, 3)
            ),
            array(
                'id'      => 2,
                'id_list' => array(2, 3)
            )
        );

        // id vs kid => id wins
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'id'  => 5,
                'cid' => array(),
                'kid' => array(
                    'name'  => 'foftest_foobar_id',
                    'value' => 4
                )
            ),
            array(
                'id'      => 5,
                'id_list' => array(5)
            )
        );

        // cid vs kid => cid wins
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'id'  => 0,
                'cid' => array(7, 8),
                'kid' => array(
                    'name'  => 'foftest_foobar_id',
                    'value' => 4
                )
            ),
            array(
                'id'      => 7,
                'id_list' => array(7, 8)
            )
        );

        $data[] = array(
            array('name' => 'foobars'),
            array(
                'id'  => 0,
                'cid' => array(),
                'kid' => array(
                    'name'  => 'foftest_foobar_id',
                    'value' => 4
                )
            ),
            array(
                'id'      => 4,
                'id_list' => array(4)
            )
        );

        return $data;
    }

	public static function getTestSetId()
	{
		$data[] = array(1);
		$data[] = array('12');
		$data[] = array(0);
		$data[] = array('0');

        return $data;
	}

	public static function getTestSetIdException()
	{
		$data[] = array(new stdClass());

		return $data;
	}

    public static function getTestSetIds()
    {
        // Correct behavior
        $data[] = array(
            array(1, '2'),
            array('id' => 1, 'id_list' => array(1, 2))
        );

        // Wrong data
        $data[] = array(
            array(array(2, 2)),
            array('id' => 0, 'id_list' => array())
        );


        $data[] = array(
            array(),
            array('id' => 0, 'id_list' => array())
        );

        // Wrong data
        $data[] = array(
            array(),
            array('id' => 0, 'id_list' => array())
        );

        $data[] = array(
            1,
            array('id' => 0,'id_list' => array())
        );

        $data[] = array(
            '1',
            array('id' => 0,'id_list' => array())
        );

        $data[] = array(
            new stdClass(),
            array('id' => 0,'id_list' => array())
        );

        return $data;
    }

    public static function getTestGetItem()
    {
		// $modelinfo, $test, $session, $checks

        // Load setting the model id
        $data[] = array(
            array('name' => 'Foobars'),
            array('setid' => 2, 'id' => null),
            array(),
            array('title' => 'Second row')
        );

        // Load by passing an id
        $data[] = array(
            array('name' => 'Foobars'),
            array('setid' => 0, 'id' => 2),
            array(),
            array('title' => 'Second row')
        );

        // No id at all
        $data[] = array(
            array('name' => 'Foobars'),
            array('setid' => 0, 'id' => 0),
            array(),
            array('title' => null)
        );

        // No id at all, but data from the session (ie a record failed the validation)
        $data[] = array(
            array('name' => 'Foobars'),
            array('setid' => 0, 'id' => 0),
            serialize(array('title' => 'Title from session')),
            array('title' => 'Title from session', 'foftest_foobar_id' => 0)
        );

        // Test vs data coming from session (record inside the db)
        $data[] = array(
            array('name' => 'Foobars'),
            array('setid' => null, 'id' => 2),
            serialize(array('foftest_foobar_id' => 2, 'title' => 'Title from session')),
            array('title' => 'Title from session')
        );

        return $data;
    }

    public static function getTestBuildQuery()
    {
        $db = JFactory::getDbo();

        // SELECT `#__foftest_foobars`.* FROM `#__foftest_foobars` ORDER BY `foftest_foobar_id` ASC
        $data[] = array(
            array('name' => 'foobars'),
            array('aliasTable' => '', 'overrideLimits' => false),
            array('query' => $db->getQuery(true)
                                ->select($db->qn('#__foftest_foobars').'.*')
                                ->from($db->qn('#__foftest_foobars'))
                                ->order($db->qn('foftest_foobar_id').' ASC')
            )
        );

        // SELECT `#__foftest_foobars`.* FROM `#__foftest_foobars`
        $data[] = array(
            array('name' => 'foobars'),
            array('aliasTable' => '', 'overrideLimits' => true),
            array('query' => $db->getQuery(true)
                    ->select($db->qn('#__foftest_foobars').'.*')
                    ->from($db->qn('#__foftest_foobars'))
            )
        );

        // SELECT `foo_alias`.* FROM `#__foftest_foobars` AS `foo_alias` ORDER BY `foo_alias`.`foftest_foobar_id` ASC
        $data[] = array(
            array('name' => 'foobars'),
            array('aliasTable' => 'foo_alias', 'overrideLimits' => false),
            array('query' => $db->getQuery(true)
                                ->select($db->qn('foo_alias').'.*')
                                ->from($db->qn('#__foftest_foobars').' AS '.$db->qn('foo_alias'))
                                ->order($db->qn('foo_alias').'.'.$db->qn('foftest_foobar_id').' ASC')
            )
        );

        return $data;
    }

    public static function getTestGetHash()
    {
        $data[] = array(
            array('name' => 'foobars'),
            array('tmpInstance' => false),
            array('hash' => 'com_foftest.foobars.')
        );

        $data[] = array(
            array('name' => 'foobar'),
            array('tmpInstance' => false),
            array('hash' => 'com_foftest.foobars.')
        );

		// Edit by Nicholas: The hash is not used in temporary model instances (using getTmpInstance), as we are
		// explicitly DO NOT want the state data to persist.
		/**
        $data[] = array(
            array('name' => 'foobars'),
            array('tmpInstance' => true),
            array('hash' => 'com_foftest.foobars.')
        );

        $data[] = array(
            array('name' => 'foobar'),
            array('tmpInstance' => true),
            array('hash' => 'com_foftest.foobars.')
        );
        /**/

        return $data;
    }

    public static function getTestGetList()
    {
        $db = JFactory::getDbo();

        // Standard query
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'query'      => $db->getQuery(true)->select('foftest_foobar_id, title, slug')->from('#__foftest_foobars'),
                'limitstart' => 0,
                'limit'      => 0,
                'group'      => '',
                'callback'   => function(&$array){}
            ),
            array(
                'list'       => array(
                    0 => (object) array('foftest_foobar_id' => 1, 'title' => 'Guinea Pig row', 'slug' => 'guinea-pig-row'),
                    1 => (object) array('foftest_foobar_id' => 2, 'title' => 'Second row', 'slug' => 'second-row'),
                    2 => (object) array('foftest_foobar_id' => 3, 'title' => 'Third row', 'slug' => 'third-row'),
                    3 => (object) array('foftest_foobar_id' => 4, 'title' => 'Fourth row', 'slug' => 'fourth-row'),
                    4 => (object) array('foftest_foobar_id' => 5, 'title' => 'Locked record', 'slug' => 'locked-record'),
                )
            )
        );

        // String query
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'query'      => (string) $db->getQuery(true)->select('foftest_foobar_id, title, slug')->from('#__foftest_foobars'),
                'limitstart' => 0,
                'limit'      => 0,
                'group'      => '',
                'callback'   => function(&$array){}
            ),
            array(
                'list'       => array(
                    0 => (object) array('foftest_foobar_id' => 1, 'title' => 'Guinea Pig row', 'slug' => 'guinea-pig-row'),
                    1 => (object) array('foftest_foobar_id' => 2, 'title' => 'Second row', 'slug' => 'second-row'),
                    2 => (object) array('foftest_foobar_id' => 3, 'title' => 'Third row', 'slug' => 'third-row'),
                    3 => (object) array('foftest_foobar_id' => 4, 'title' => 'Fourth row', 'slug' => 'fourth-row'),
                    4 => (object) array('foftest_foobar_id' => 5, 'title' => 'Locked record', 'slug' => 'locked-record'),
                )
            )
        );

        // Standard query with limit
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'query'      => $db->getQuery(true)->select('foftest_foobar_id, title, slug')->from('#__foftest_foobars'),
                'limitstart' => 0,
                'limit'      => 3,
                'group'      => '',
                'callback'   => function(&$array){}
            ),
            array(
                'list'       => array(
                    0 => (object) array('foftest_foobar_id' => 1, 'title' => 'Guinea Pig row', 'slug' => 'guinea-pig-row'),
                    1 => (object) array('foftest_foobar_id' => 2, 'title' => 'Second row', 'slug' => 'second-row'),
                    2 => (object) array('foftest_foobar_id' => 3, 'title' => 'Third row', 'slug' => 'third-row')
                )
            )
        );

        // Standard query with limit and limitstart
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'query'      => $db->getQuery(true)->select('foftest_foobar_id, title, slug')->from('#__foftest_foobars'),
                'limitstart' => 2,
                'limit'      => 3,
                'group'      => '',
                'callback'   => function(&$array){}
            ),
            array(
                'list'       => array(
                    0 => (object) array('foftest_foobar_id' => 3, 'title' => 'Third row', 'slug' => 'third-row'),
                    1 => (object) array('foftest_foobar_id' => 4, 'title' => 'Fourth row', 'slug' => 'fourth-row'),
                    2 => (object) array('foftest_foobar_id' => 5, 'title' => 'Locked record', 'slug' => 'locked-record'),
                )
            )
        );

        // Standard query with groups
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'query'      => $db->getQuery(true)->select('foftest_foobar_id, title, slug')->from('#__foftest_foobars'),
                'limitstart' => 0,
                'limit'      => 0,
                'group'      => 'slug',
                'callback'   => function(&$array){}
            ),
            array(
                'list'       => array(
                    'guinea-pig-row' => (object) array('foftest_foobar_id' => 1, 'title' => 'Guinea Pig row', 'slug' => 'guinea-pig-row'),
                    'second-row'     => (object) array('foftest_foobar_id' => 2, 'title' => 'Second row', 'slug' => 'second-row'),
                    'third-row'      => (object) array('foftest_foobar_id' => 3, 'title' => 'Third row', 'slug' => 'third-row'),
                    'fourth-row'     => (object) array('foftest_foobar_id' => 4, 'title' => 'Fourth row', 'slug' => 'fourth-row'),
                    'locked-record'  => (object) array('foftest_foobar_id' => 5, 'title' => 'Locked record', 'slug' => 'locked-record'),
                )
            )
        );

        // Let's manipulate the result array
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'query'      => $db->getQuery(true)->select('foftest_foobar_id, title, slug')->from('#__foftest_foobars'),
                'limitstart' => 0,
                'limit'      => 0,
                'group'      => '',
                'callback'   => function(&$array){foreach($array as $item){ $item->title = $item->title.'XXX';}}
            ),
            array(
                'list'       => array(
                    0 => (object) array('foftest_foobar_id' => 1, 'title' => 'Guinea Pig rowXXX', 'slug' => 'guinea-pig-row'),
                    1 => (object) array('foftest_foobar_id' => 2, 'title' => 'Second rowXXX', 'slug' => 'second-row'),
                    2 => (object) array('foftest_foobar_id' => 3, 'title' => 'Third rowXXX', 'slug' => 'third-row'),
                    3 => (object) array('foftest_foobar_id' => 4, 'title' => 'Fourth rowXXX', 'slug' => 'fourth-row'),
                    4 => (object) array('foftest_foobar_id' => 5, 'title' => 'Locked recordXXX', 'slug' => 'locked-record'),
                )
            )
        );

        return $data;
    }

    public static function getTestGetItemList()
    {
        $db = JFactory::getDbo();

        $data[] = array(
            array('name' => 'foobars'),
            array(
                'query'      => $db->getQuery(true)->select('foftest_foobar_id, title, slug')->from('#__foftest_foobars'),
                'limitstart' => 0,
                'limit'      => 0,
                'group'      => '',
                'override'   => false
            ),
            array(
                'list'       => array(
                    0 => (object) array('foftest_foobar_id' => 1, 'title' => 'Guinea Pig row', 'slug' => 'guinea-pig-row'),
                    1 => (object) array('foftest_foobar_id' => 2, 'title' => 'Second row', 'slug' => 'second-row'),
                    2 => (object) array('foftest_foobar_id' => 3, 'title' => 'Third row', 'slug' => 'third-row'),
                    3 => (object) array('foftest_foobar_id' => 4, 'title' => 'Fourth row', 'slug' => 'fourth-row'),
                    4 => (object) array('foftest_foobar_id' => 5, 'title' => 'Locked record', 'slug' => 'locked-record'),
                )
            )
        );

        $data[] = array(
            array('name' => 'foobars'),
            array(
                'query'      => $db->getQuery(true)->select('foftest_foobar_id, title, slug')->from('#__foftest_foobars'),
                'limitstart' => 1,
                'limit'      => 2,
                'group'      => '',
                'override'   => false
            ),
            array(
                'list'       => array(
                    0 => (object) array('foftest_foobar_id' => 2, 'title' => 'Second row', 'slug' => 'second-row'),
                    1 => (object) array('foftest_foobar_id' => 3, 'title' => 'Third row', 'slug' => 'third-row'),
                )
            )
        );

        // Overriding the limits (limitstart and limit set)
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'query'      => $db->getQuery(true)->select('foftest_foobar_id, title, slug')->from('#__foftest_foobars'),
                'limitstart' => 1,
                'limit'      => 2,
                'group'      => '',
                'override'   => true
            ),
            array(
                'list'       => array(
                    0 => (object) array('foftest_foobar_id' => 1, 'title' => 'Guinea Pig row', 'slug' => 'guinea-pig-row'),
                    1 => (object) array('foftest_foobar_id' => 2, 'title' => 'Second row', 'slug' => 'second-row'),
                    2 => (object) array('foftest_foobar_id' => 3, 'title' => 'Third row', 'slug' => 'third-row'),
                    3 => (object) array('foftest_foobar_id' => 4, 'title' => 'Fourth row', 'slug' => 'fourth-row'),
                    4 => (object) array('foftest_foobar_id' => 5, 'title' => 'Locked record', 'slug' => 'locked-record'),
                )
            )
        );

        $data[] = array(
            array('name' => 'foobars'),
            array(
                'query'      => $db->getQuery(true)->select('foftest_foobar_id, title, slug')->from('#__foftest_foobars'),
                'limitstart' => 1,
                'limit'      => 2,
                'group'      => 'slug',
                'override'   => false
            ),
            array(
                'list'       => array(
                    'second-row' => (object) array('foftest_foobar_id' => 2, 'title' => 'Second row', 'slug' => 'second-row'),
                    'third-row' => (object) array('foftest_foobar_id' => 3, 'title' => 'Third row', 'slug' => 'third-row'),
                )
            )
        );

        return $data;
    }

    public static function getTestGetIterator()
    {
        $data[] = array(
            array(
                'override'   => false,
                'tableClass' => null,
                'limit'      => 3,
                'limitstart' => 1
            ),
            array(
                'count'      => 3,
                'tableClass' => 'F0FTable'
            )
        );

        $data[] = array(
            array(
                'override'   => false,
                'tableClass' => null,
                'limit'      => 3,
                'limitstart' => 4
            ),
            array(
                'count' => 1,
                'tableClass' => 'F0FTable'
            )
        );

        $data[] = array(
            array(
                'override'   => true,
                'tableClass' => null,
                'limit'      => 3,
                'limitstart' => 4
            ),
            array(
                'count' => 5,
                'tableClass' => 'F0FTable'
            )
        );

        $data[] = array(
            array(
                'override'   => false,
                'tableClass' => 'FofcustomTableFoobar',
                'limit'      => 3,
                'limitstart' => 1
            ),
            array(
                'count'      => 3,
                'tableClass' => 'FofcustomTableFoobar'
            )
        );

        return $data;
    }

    public static function getTestSave()
    {
        // SAVE OK - Array data, no table id inside - No form
        $data[] = array(
            array(
                'data'     => array(
                    'title'   => 'Test save',
                    'enabled' => 1
                ),
                'form'     => false,
                'table'    => array(
                    'save'    => array(
                        'id'     => 88,
                        'return' => true
                    ),
                    'error' => array()
                ),
                'onBefore' => array(
                    // I don't want to modify incoming args inside the onBefore event
                    'return'  => true
                ),
                'onAfter'  => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'return'  => true,
                'table'   => array(
                    'id'     => 88
                )
            )
        );

        // SAVE OK - Array data, table id inside - No form
        $data[] = array(
            array(
                'data'     => array(
                    'foftest_foobar_id' => 2,
                    'title'   => 'Test save',
                    'enabled' => 1
                ),
                'form'     => false,
                'table'    => array(
                    'save'    => array(
                        'return' => true
                    ),
                    'error' => array()
                ),
                'onBefore' => array(
                    // I don't want to modify incoming args inside the onBefore event
                    'return'  => true
                ),
                'onAfter'  => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'return'  => true,
                'table'   => array(
                    'id'     => 2
                )
            )
        );

        // SAVE OK - Array data, table id inside - No form
        $data[] = array(
            array(
                'data'     => array(
                    'foftest_foobar_id' => 32,
                    'title'   => 'Test save',
                    'enabled' => 1
                ),
                'form'     => false,
                'table'    => array(
                    'save'    => array(
                        'id'     => 40,
                        'return' => true
                    ),
                    'error' => array()
                ),
                'onBefore' => array(
                    // I don't want to modify incoming args inside the onBefore event
                    'return'  => true
                ),
                'onAfter'  => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'return'  => true,
                'table'   => array(
                    'id'     => 40
                )
            )
        );

        // SAVE OK - Object data, table id inside - No form
        $data[] = array(
            array(
                'data'     => (object) array(
                    'foftest_foobar_id' => 2,
                    'title'   => 'Test save',
                    'enabled' => 1
                ),
                'form'     => false,
                'table'    => array(
                    'save'    => array(
                        'return' => true
                    ),
                    'error' => array()
                ),
                'onBefore' => array(
                    // I don't want to modify incoming args inside the onBefore event
                    'return'  => true
                ),
                'onAfter'  => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'return'  => true,
                'table'   => array(
                    'id'     => 2
                )
            )
        );

        // SAVE OK - F0FTable data, table id inside - No form
        $config['input'] = new F0FInput(array('option' => 'com_foftest', 'view' => 'foobar'));
        $table = clone F0FTable::getAnInstance('Foobar', 'FoftestTable', $config);

        $data[] = array(
            array(
                'data'     => $table,
                'loadid'   => 3,
                'form'     => false,
                'table'    => array(
                    'save'    => array(
                        'return' => true
                    ),
                    'error' => array()
                ),
                'onBefore' => array(
                    // I don't want to modify incoming args inside the onBefore event
                    'return'  => true
                ),
                'onAfter'  => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'return'  => true,
                'table'   => array(
                    'id'     => 3
                )
            )
        );

        // SAVE OK - Array data, no table id inside - WITH form validation (TRUE)
        $data[] = array(
            array(
                'data'     => array(
                    'title'   => 'Test save',
                    'enabled' => 1
                ),
                'form'     => array(
                    'mock'    => 1,
                    'validation' => true,
                    'validationResult' => true
                ),
                'table'    => array(
                    'save'    => array(
                        'id'     => 88,
                        'return' => true
                    ),
                    'error' => array()
                ),
                'onBefore' => array(
                    // I don't want to modify incoming args inside the onBefore event
                    'return'  => true
                ),
                'onAfter'  => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'return'  => true,
                'table'   => array(
                    'id'     => 88
                )
            )
        );

        // SAVE OK - Array data, no table id inside - WITHOUT form validation
        $data[] = array(
            array(
                'data'     => array(
                    'title'   => 'Test save',
                    'enabled' => 1
                ),
                'form'     => array(
                    'mock'    => 1,
                    'validation' => false
                ),
                'table'    => array(
                    'save'    => array(
                        'id'     => 88,
                        'return' => true
                    ),
                    'error' => array()
                ),
                'onBefore' => array(
                    // I don't want to modify incoming args inside the onBefore event
                    'return'  => true
                ),
                'onAfter'  => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'return'  => true,
                'table'   => array(
                    'id'     => 88
                )
            )
        );

        // SAVE OK - Array data, no table id inside - No form - onBefore changes $data
        $data[] = array(
            array(
                'data'     => array(
                    'title'   => 'Test save',
                    'enabled' => 1
                ),
                'form'     => false,
                'table'    => array(
                    'save'    => array(
                        'id'     => 88,
                        'return' => true
                    ),
                    'error' => array()
                ),
                'onBefore' => array(
                    'modify'  => 1,
                    'table'   => function($table){return $table;},
                    'allData' => array(
                        'title' => 'Test modified'
                    ),
                    'return'  => true
                ),
                'onAfter'  => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'save'    => array(
                    'data'   => array(
                        'title' => 'Test modified'
                    )
                ),
                'return'  => true,
                'table'   => array(
                    'id'     => 88
                )
            )
        );

        // ONBEFORESAVE ERROR - Array data, no table id inside - No form
        $data[] = array(
            array(
                'data'     => array(
                    'title'   => 'Test save',
                    'enabled' => 1
                ),
                'form'     => false,
                'table'    => array(
                    'save'    => array(
                        'return' => true
                    ),
                    'error' => array()
                ),
                'onBefore' => array(
                    // I don't want to modify incoming args inside the onBefore event
                    'return'  => false
                ),
                'onAfter'  => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'return'  => false
            )
        );

        // SAVE ERROR - Array data, no table id inside - No form
        $data[] = array(
            array(
                'data'     => array(
                    'title'   => 'Test save',
                    'enabled' => 1
                ),
                'form'     => false,
                'table'    => array(
                    'save'    => array(
                        'return' => false
                    ),
                    'error' => array('Test error')
                ),
                'onBefore' => array(
                    // I don't want to modify incoming args inside the onBefore event
                    'return'  => true
                ),
                'onAfter'  => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'table'   => array(
                    'error'  => array('Test error')
                ),
                'return'  => false
            )
        );

        // SAVE ERROR - Array data, no table id inside - WITH form validation (FALSE)
        $data[] = array(
            array(
                'data'     => array(
                    'title'   => 'Test save',
                    'enabled' => 1
                ),
                'form'     => array(
                    'mock'    => 1,
                    'validation' => true,
                    'validationResult' => false
                ),
                'table'    => array(
                    'save'    => array(
                        'id'     => 88,
                        'return' => true
                    ),
                    'error' => array()
                ),
                'onBefore' => array(
                    // I don't want to modify incoming args inside the onBefore event
                    'return'  => true
                ),
                'onAfter'  => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'return'  => false,
            )
        );

        return $data;
    }

    public static function getTestSaveSessionWipe()
    {
        // SAVE OK - Array data, no table id inside
        $data[] = array(
            array(
                'data'     => array(
                    'title'   => 'Test save',
                    'enabled' => 1
                ),
                'table'    => array(
                    'save'       => true,
                    'error'      => array(),
                    'properties' => array()
                    ),
                ),
            array(
                'return'  => true
            )
        );

        // SAVE ERROR - Array data, no table id inside
        $data[] = array(
            array(
                'data'     => array(
                    'title'   => 'Test save',
                    'enabled' => 1
                ),
                'table'    => array(
                    'save'       => false,
                    'error'      => array('Test error'),
                    'properties' => array(
                        'title'   => 'Test save',
                        'enabled' => 1,
                        'input'   => 'test',
                        'config'  => array(
                            'input' => 123,
                            'db'    => 123,
                            'dbo'   => 123,
                            'ok'    => 'ok'
                        ))
                ),
            ),
            array(
                'return'     => false,
                'session'    => serialize(array(
                    'title'   => 'Test save',
                    'enabled' => 1,
                    'config'  => array(
                        'ok'    => 'ok'
                    )))
            )
        );

        return $data;
    }

    public static function getTestCopy()
    {
        // Everything fine
        $data[] = array(
            array(
                'id_list'  => array(2),
                'copy'     => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => true)
        );

        // Empty id_list
        $data[] = array(
            array(
                'id_list'  => array(),
                'copy'     => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => true)
        );

        // Wrong type id_list
        $data[] = array(
            array(
                'id_list'  => '',
                'copy'     => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => true)
        );

        // Copy returns an error
        $data[] = array(
            array(
                'id_list'  => array(2, 3),
                'copy'     => false,
                'error'    => 'Copy returned false',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onBeforeCopy returns an error
        $data[] = array(
            array(
                'id_list'  => array(2, 3),
                'copy'     => false,
                'error'    => '',
                'onBefore' => false,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onBeforeCopy returns an error (copy would be ok)
        $data[] = array(
            array(
                'id_list'  => array(2, 3),
                'copy'     => true,
                'error'    => '',
                'onBefore' => false,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onAfterCopy returns an error (copy would be ok)
        $data[] = array(
            array(
                'id_list'  => array(2, 3),
                'copy'     => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => false
            ),
            array('return' => true)
        );

        return $data;
    }

    public static function getTestDelete()
    {
        // Everything fine
        $data[] = array(
            array(
                'id_list'  => array(2),
                'delete'   => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => true)
        );

        // Empty id_list
        $data[] = array(
            array(
                'id_list'  => array(),
                'delete'   => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => true)
        );

        // Wrong type id_list
        $data[] = array(
            array(
                'id_list'  => '',
                'delete'   => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => true)
        );

        // Delete returns an error
        $data[] = array(
            array(
                'id_list'  => array(2, 3),
                'delete'   => false,
                'error'    => 'Delete returned false',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onBeforeDelete returns an error
        $data[] = array(
            array(
                'id_list'  => array(2, 3),
                'delete'   => false,
                'error'    => '',
                'onBefore' => false,
                'onAfter'  => true
            ),
            array('return' => true)
        );

        // onBeforeDelete returns an error just once (delete fails)
        $data[] = array(
            array(
                'id_list'  => array(2, 3),
                'delete'   => false,
                'beforeFailsOnce' => true,
                'error'    => '',
                'onBefore' => false,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onBeforeDelete returns an error just once (delete works)
        $data[] = array(
            array(
                'id_list'  => array(2, 3),
                'delete'   => true,
                'beforeFailsOnce' => true,
                'error'    => '',
                'onBefore' => false,
                'onAfter'  => true
            ),
            array('return' => true)
        );

        // onBeforeDelete returns an error (copy would be ok)
        $data[] = array(
            array(
                'id_list'  => array(2, 3),
                'delete'   => true,
                'error'    => '',
                'onBefore' => false,
                'onAfter'  => true
            ),
            array('return' => true)
        );

        // onAfterDelete returns an error (copy would be ok)
        $data[] = array(
            array(
                'id_list'  => array(2, 3),
                'delete'   => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => false
            ),
            array('return' => true)
        );

        return $data;
    }

    public static function getTestPublish()
    {
        // Everything fine
        $data[] = array(
            array(
                'id_list'  => array(2),
                'publish'  => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => true)
        );

        // Empty id_list
        $data[] = array(
            array(
                'id_list'  => array(),
                'publish'  => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => true)
        );

        // Wrong type id_list
        $data[] = array(
            array(
                'id_list'  => '',
                'publish'  => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => true)
        );

        // Publish returns an error
        $data[] = array(
            array(
                'id_list'  => array(2, 3),
                'publish'  => false,
                'error'    => 'Publish returned false',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onBeforePublish returns an error
        $data[] = array(
            array(
                'id_list'  => array(2, 3),
                'publish'  => false,
                'error'    => '',
                'onBefore' => false,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onBeforePublish returns an error (publish would be ok)
        $data[] = array(
            array(
                'id_list'  => array(2, 3),
                'publish'  => true,
                'error'    => '',
                'onBefore' => false,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onAfterPublish returns an error (publish would be ok)
        $data[] = array(
            array(
                'id_list'  => array(2, 3),
                'publish'  => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => false
            ),
            array('return' => true)
        );

        return $data;
    }

    public static function getTestCheckout()
    {
        // Everything fine
        $data[] = array(
            array(
                'checkout' => true,
                'error'    => '',
            ),
            array('return' => true)
        );

        // Error
        $data[] = array(
            array(
                'checkout' => false,
                'error'    => 'Checkout error',
            ),
            array('return' => false)
        );

        return $data;
    }

    public static function getTestCheckin()
    {
        // Everything fine
        $data[] = array(
            array(
                'checkin'  => true,
                'error'    => '',
            ),
            array('return' => true)
        );

        // Error
        $data[] = array(
            array(
                'checkin'  => false,
                'error'    => 'Checkin error',
            ),
            array('return' => false)
        );

        return $data;
    }

    public static function getTestIsCheckedOut()
    {
        // Everything fine
        $data[] = array(
            array(
                'isCheckedOut'  => true,
                'error'         => '',
            ),
            array('return' => true)
        );

        // Error
        $data[] = array(
            array(
                'isCheckedOut'  => false,
                'error'         => 'isCheckedOut error',
            ),
            array('return' => false)
        );

        return $data;
    }

    public static function getTestHit()
    {
        // Everything fine
        $data[] = array(
            array(
                'hit'     => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => true)
        );

        // Hit returns an error
        $data[] = array(
            array(
                'hit'      => false,
                'error'    => 'Hit returned false',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onBeforeHit returns an error
        $data[] = array(
            array(
                'hit'      => false,
                'error'    => '',
                'onBefore' => false,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onBeforeHit returns an error (hit would be ok)
        $data[] = array(
            array(
                'hit'      => true,
                'error'    => '',
                'onBefore' => false,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onAfterHit returns an error (hit would be ok)
        $data[] = array(
            array(
                'hit'      => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => false
            ),
            array('return' => true)
        );

        return $data;
    }

    public static function getTestMove()
    {
        // Everything fine
        $data[] = array(
            array(
                'load'     => true,
                'move'     => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => true)
        );

        // Load returns an error
        $data[] = array(
            array(
                'load'     => false,
                'move'     => true,
                'error'    => 'Load returned false',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // Move returns an error
        $data[] = array(
            array(
                'load'     => true,
                'move'     => false,
                'error'    => 'Move returned false',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onBeforeMove returns an error
        $data[] = array(
            array(
                'load'     => true,
                'move'     => false,
                'error'    => '',
                'onBefore' => false,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onBeforeMove returns an error (move would be ok)
        $data[] = array(
            array(
                'load'     => true,
                'move'     => true,
                'error'    => '',
                'onBefore' => false,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onAfterMove returns an error (move would be ok)
        $data[] = array(
            array(
                'load'     => true,
                'move'     => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => false
            ),
            array('return' => true)
        );

        return $data;
    }

    public static function getTestReorder()
    {
        // Everything fine
        $data[] = array(
            array(
                'reorder'  => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => true)
        );

        // Reorder returns an error
        $data[] = array(
            array(
                'reorder'  => false,
                'error'    => 'Reorder returned false',
                'onBefore' => true,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onBeforeReorder returns an error
        $data[] = array(
            array(
                'reorder'  => false,
                'error'    => '',
                'onBefore' => false,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onBeforeReorder returns an error (reorder would be ok)
        $data[] = array(
            array(
                'reorder'  => true,
                'error'    => '',
                'onBefore' => false,
                'onAfter'  => true
            ),
            array('return' => false)
        );

        // onAfterReorder returns an error (reorder would be ok)
        $data[] = array(
            array(
                'reorder'  => true,
                'error'    => '',
                'onBefore' => true,
                'onAfter'  => false
            ),
            array('return' => false)
        );

        return $data;
    }

    public static function getTestgetTotal()
    {
        $db = JFactory::getDbo();

        $data[] = array(
            array(
                'buildCount' => false,
                'buildQuery' => $db->getQuery(true)->select('*')->from('#__foftest_foobars')->where('foftest_foobar_id <= 5')
            ),
            array('total' => 5)
        );

        $data[] = array(
            array(
                'buildCount' => $db->getQuery(true)->select('COUNT(*)')->from('#__foftest_foobars')->where('foftest_foobar_id <= 2'),
                'buildQuery' => ''
            ),
            array('total' => 2)
        );

        return $data;
    }

    public static function getTestGetTable()
    {
		// $modelinfo, $test
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'name'   => 'Foobar', 'prefix'  => 'FoftestTable', 'options' => array(),
                'create' => array(
                    'name'    => 'Foobar',
                    'prefix'  => 'FoftestTable',
                    'options' => ''
                )
            )
        );

        $data[] = array(
            array('name' => 'foobars'),
            array(
                'name'   => 'Foobar', 'prefix'  => null, 'options' => array(),
                'create' => array(
                    'name'    => 'Foobar',
                    'prefix'  => 'com_foftest',
                    'options' => ''
                )
            )
        );

        $data[] = array(
            array('name' => 'foobars'),
            array(
                'name'   => '', 'prefix'  => null, 'options' => array(),
                'create' => array(
                    'name'    => 'foobar',
                    'prefix'  => 'com_foftest',
                    'options' => ''
                )
            )
        );

        $data[] = array(
            array('name' => 'foobars'),
            array(
                'name'   => '', 'prefix'  => null, 'options' => array(), 'wipeTable' => true,
                'create' => array(
                    'name'    => 'foobar',
                    'prefix'  => 'com_foftest',
                    'options' => ''
                )
            )
        );

        return $data;
    }

    public static function getTestCreateTable()
    {
        // Standard call
        $data[] = array(
            array('name' => 'Foobars'),
            array('name' => 'Foobar', 'prefix' => 'FoftestTable', 'config' => array()),
            array('name' => 'Foobar', 'prefix' => 'FoftestTable')
        );

        // Standard call - with dbo
        $data[] = array(
            array('name' => 'Foobars'),
            array('name' => 'Foobar', 'prefix' => 'FoftestTable', 'config' => array(), 'loadDbo' => true),
            array('name' => 'Foobar', 'prefix' => 'FoftestTable')
        );

        // Wrong configuration param
        $data[] = array(
            array('name' => 'Foobars'),
            array('name' => 'Foobar', 'prefix' => 'FoftestTable', 'config' => ''),
            array('name' => 'Foobar', 'prefix' => 'FoftestTable')
        );

        // Wrong configuration param (2)
        $data[] = array(
            array('name' => 'Foobars'),
            array('name' => 'Foobar', 'prefix' => 'FoftestTable', 'config' => new stdClass()),
            array('name' => 'Foobar', 'prefix' => 'FoftestTable')
        );

        // Wrong table name and table prefix
        $data[] = array(
            array('name' => 'Foobars'),
            array('name' => '$$$Foobar', 'prefix' => '$$$$$$$$FoftestTable', 'config' => array()),
            array('name' => 'Foobar', 'prefix' => 'FoftestTable')
        );

        return $data;
    }

    public static function getTestGetForm()
    {
        // Standard behavior
        $form   = new F0FForm('dummy');
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'data'      => array(),
                'loadData'  => true,
                'source'    => 'form.foobars',
                'form_name' => '',
                'loadForm'  => clone $form,
                'onBefore'  => array(
                    // I don't want to modify incoming args inside the onBefore event
                ),
                'onAfter'   => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'onBefore' => array(
                    'name'    => 'com_foftest.foobars.form.foobars',
                    'source'  => 'form.foobars',
                    'options' => array('control' => false, 'load_data' => true)),
                'loadForm' => array(
                    'name'    => 'com_foftest.foobars.form.foobars',
                    'source'  => 'form.foobars',
                    'options' => array('control' => false, 'load_data' => true)),
                'onAfter'  => array(
                    'form'    => clone $form,
                    'name'    => 'com_foftest.foobars.form.foobars',
                    'source'  => 'form.foobars',
                    'options' => array('control' => false, 'load_data' => true)),
                'form'     => clone $form
            )
        );

        // Getting the form name from the request
        $form   = new F0FForm('dummy');
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'data'      => array(),
                'loadData'  => true,
                'source'    => null,
                'form_name' => 'form.fromrequest',
                'loadForm'  => clone $form,
                'onBefore'  => array(
                    // I don't want to modify incoming args inside the onBefore event
                ),
                'onAfter'   => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'onBefore' => array(
                    'name'    => 'com_foftest.foobars.form.fromrequest',
                    'source'  => 'form.fromrequest',
                    'options' => array('control' => false, 'load_data' => true)),
                'loadForm' => array(
                    'name'    => 'com_foftest.foobars.form.fromrequest',
                    'source'  => 'form.fromrequest',
                    'options' => array('control' => false, 'load_data' => true)),
                'onAfter'  => array(
                    'form'    => clone $form,
                    'name'    => 'com_foftest.foobars.form.fromrequest',
                    'source'  => 'form.fromrequest',
                    'options' => array('control' => false, 'load_data' => true)),
                'form'     => clone $form
            )
        );

        // Calculating the form name on your own
        $form   = new F0FForm('dummy');
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'data'      => array(),
                'loadData'  => true,
                'source'    => null,
                'form_name' => null,
                'loadForm'  => clone $form,
                'onBefore'  => array(
                    // I don't want to modify incoming args inside the onBefore event
                ),
                'onAfter'   => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'onBefore' => array(
                    'name'    => 'com_foftest.foobars.form.foobars',
                    'source'  => 'form.foobars',
                    'options' => array('control' => false, 'load_data' => true)),
                'loadForm' => array(
                    'name'    => 'com_foftest.foobars.form.foobars',
                    'source'  => 'form.foobars',
                    'options' => array('control' => false, 'load_data' => true)),
                'onAfter'  => array(
                    'form'    => clone $form,
                    'name'    => 'com_foftest.foobars.form.foobars',
                    'source'  => 'form.foobars',
                    'options' => array('control' => false, 'load_data' => true)),
                'form'     => clone $form
            )
        );

        // loadForm returns false
        $form   = false;
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'data'      => array(),
                'loadData'  => true,
                'source'    => null,
                'form_name' => 'form.foobars',
                'loadForm'  => false,
                'onBefore'  => array(
                    // I don't want to modify incoming args inside the onBefore event
                ),
                'onAfter'   => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'onBefore' => array(
                    'name'    => 'com_foftest.foobars.form.foobars',
                    'source'  => 'form.foobars',
                    'options' => array('control' => false, 'load_data' => true)),
                'loadForm' => array(
                    'name'    => 'com_foftest.foobars.form.foobars',
                    'source'  => 'form.foobars',
                    'options' => array('control' => false, 'load_data' => true)),
                'onAfter'  => array(
                    'form'    => false,
                    'name'    => 'com_foftest.foobars.form.foobars',
                    'source'  => 'form.foobars',
                    'options' => array('control' => false, 'load_data' => true)),
                'form'     => false
            )
        );

        // Changing arguments in the onBefore event
        $form   = new F0FForm('dummy');
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'data'      => array(),
                'loadData'  => true,
                'source'    => 'form.foobars',
                'form_name' => null,
                'loadForm'  => clone $form,
                'onBefore'  => array(
                    'modify'  => 1,
                    'name'    => 'com_foftest.onbeforemodified',
                    'source'  => 'form.onbeforemodified',
                    'options' => array('control' => false, 'load_data' => false)
                ),
                'onAfter'   => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'onBefore' => array(
                    'name'    => 'com_foftest.foobars.form.foobars',
                    'source'  => 'form.foobars',
                    'options' => array('control' => false, 'load_data' => true)),
                'loadForm' => array(
                    'name'    => 'com_foftest.onbeforemodified',
                    'source'  => 'form.onbeforemodified',
                    'options' => array('control' => false, 'load_data' => false)),
                'onAfter'  => array(
                    'form'    => clone $form,
                    'name'    => 'com_foftest.onbeforemodified',
                    'source'  => 'form.onbeforemodified',
                    'options' => array('control' => false, 'load_data' => false)),
                'form'     => clone $form
            )
        );

        // Changing arguments in the onAfter event
        $form   = new F0FForm('dummy');
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'data'      => array(),
                'loadData'  => true,
                'source'    => 'form.foobars',
                'form_name' => null,
                'loadForm'  => clone $form,
                'onBefore'  => array(
                    'modify'  => 1,
                    'name'    => 'com_foftest.onbeforemodified',
                    'source'  => 'form.onbeforemodified',
                    'options' => array('control' => false, 'load_data' => false)
                ),
                'onAfter'   => array(
                    'modify'  => 1,
                    'form'    => false
                )
            ),
            array(
                'onBefore' => array(
                    'name'    => 'com_foftest.foobars.form.foobars',
                    'source'  => 'form.foobars',
                    'options' => array('control' => false, 'load_data' => true)),
                'loadForm' => array(
                    'name'    => 'com_foftest.onbeforemodified',
                    'source'  => 'form.onbeforemodified',
                    'options' => array('control' => false, 'load_data' => false)),
                'onAfter'  => array(
                    'form'    => clone $form,
                    'name'    => 'com_foftest.onbeforemodified',
                    'source'  => 'form.onbeforemodified',
                    'options' => array('control' => false, 'load_data' => false)),
                'form'     => false
            )
        );

        return $data;
    }

    public static function getTestLoadForm()
    {
        // Standard behavior
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'formPath'  => JPATH_ROOT.'/administrator/components/com_foftest/views/foobars/tmpl/form.browse.xml',
                'data'      => array(2),
                'name'      => 'com_foftest.foobars',
                'source'    => 'form.browse',
                'options'   => array('load_data' => true),
                'clear'     => false,
                'xpath'     => false,
                'onBefore'  => array(
                    // I don't want to modify incoming args inside the onBefore event
                ),
                'onAfter'   => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'onBefore' => array(
                    'data'    => array(2)),
                'onPre'    => array(
                    'data'    => array(2)),
                'onAfter'  => array(
                    'data'    => array(2)),
                'bind'     => array(
                    'data' => array(2)),
                'form'     => true
            )
        );

        // Form filename not found
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'formPath'  => false,
                'data'      => array(),
                'name'      => 'com_foftest.foobars',
                'source'    => 'form.browse',
                'options'   => array('load_data' => true),
                'clear'     => false,
                'xpath'     => false,
                'onBefore'  => array(
                    // I don't want to modify incoming args inside the onBefore event
                ),
                'onAfter'   => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'onBefore' => array(
                    'data'    => array()),
                'onPre'    => array(
                    'data'    => array()),
                'onAfter'  => array(
                    'data'    => array()),
                'bind'     => array(
                    'data' => array()),
                'form'     => false
            )
        );

        // Don't load form data
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'formPath'  => JPATH_ROOT.'/administrator/components/com_foftest/views/foobars/tmpl/form.browse.xml',
                'data'      => '',
                'name'      => 'com_foftest.foobars',
                'source'    => 'form.browse',
                'options'   => array('load_data' => false),
                'clear'     => false,
                'xpath'     => false,
                'onBefore'  => array(
                    // I don't want to modify incoming args inside the onBefore event
                ),
                'onAfter'   => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'onBefore' => array(
                    'data'    => array()),
                'onPre'    => array(
                    'data'    => array()),
                'onAfter'  => array(
                    'data'    => array()),
                'bind'     => array(
                    'data' => array()),
                'form'     => true
            )
        );

        // onBefore changes stuff
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'formPath'  => JPATH_ROOT.'/administrator/components/com_foftest/views/foobars/tmpl/form.browse.xml',
                'data'      => array(2),
                'name'      => 'com_foftest.foobars',
                'source'    => 'form.browse',
                'options'   => array('load_data' => true),
                'clear'     => false,
                'xpath'     => false,
                'onBefore'  => array(
                    'modify'   => 1,
                    'form'     => function($form){$form->dummy += 2; return $form;},
                    'data'     => array(4)
                ),
                'onAfter'   => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'onBefore' => array(
                    'data'    => array(2)),
                'onPre'    => array(
                    'data'    => array(4)),
                'onAfter'  => array(
                    'data'    => array(4)),
                'bind'     => array(
                    'data' => array(4)),
                'form'     => true
            )
        );

        // onAfter changes stuff
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'formPath'  => JPATH_ROOT.'/administrator/components/com_foftest/views/foobars/tmpl/form.browse.xml',
                'data'      => array(2),
                'name'      => 'com_foftest.foobars',
                'source'    => 'form.browse',
                'options'   => array('load_data' => true),
                'clear'     => false,
                'xpath'     => false,
                'onBefore'  => array(
                    'modify'   => 1,
                    'form'     => function($form){$form->dummy += 2; return $form;},
                    'data'     => array(4)
                ),
                'onAfter'   => array(
                    'modify'   => 1,
                    'form'     => function($form){$form->dummy += 5; return $form;},
                    'data'     => array(8)
                )
            ),
            array(
                'onBefore' => array(
                    'data'    => array(2)),
                'onPre'    => array(
                    'data'    => array(4)),
                'onAfter'  => array(
                    'data'    => array(4)),
                'bind'     => array(
                    'data' => array(8)),
                'form'     => true
            )
        );

        // An Exception has been thrown
        $data[] = array(
            array('name' => 'foobars'),
            array(
                'formPath'  => JPATH_ROOT.'/administrator/components/com_foftest/views/foobars/tmpl/form.browse.xml',
                'data'      => array(2),
                'name'      => 'com_foftest.foobars',
                'source'    => 'form.browse',
                'options'   => array('load_data' => true),
                'clear'     => false,
                'xpath'     => false,
                'onBefore'  => array(
                    'modify'   => 1,
                    'form'     => function($form){throw new Exception('Test exception');},
                    'data'     => array(4)
                ),
                'onAfter'   => array(
                    // I don't want to modify incoming args inside the onAfter event
                )
            ),
            array(
                'onBefore' => array(
                    'data'    => array(2)),
                'onPre'    => array(
                    'data'    => array(4)),
                'onAfter'  => array(
                    'data'    => array(4)),
                'bind'     => array(
                    'data' => array(4)),
                'form'     => false,
                'errMsg'   => 'Test exception'
            )
        );

        return $data;
    }

    public static function getTestFindFormFilename()
    {
        $origpaths = array(
            JPATH_ROOT.'/administrator/templates/system/foobars/',
            JPATH_ROOT.'/administrator/templates/system/foobar/',
            JPATH_ROOT.'/administrator/components/com_foftest/views/foobars/tmpl',
            JPATH_ROOT.'/administrator/components/com_foftest/views/foobar/tmpl',
            JPATH_ROOT.'/components/com_foftest/views/foobars/tmpl',
            JPATH_ROOT.'/components/com_foftest/views/foobar/tmpl',
            JPATH_ROOT.'/components/com_foftest/models/forms',
            JPATH_ROOT.'/administrator/components/com_foftest/models/forms',
        );

        foreach($origpaths as &$path)
        {
            $path  = trim(str_replace('\\', '/', $path), '/');
        }

        // Form not found
        $paths  = $origpaths;
        $data[] = array(
            array('name' => 'Foobars'),
            array(
                'form_name' => 'form.browse',
                'structure' => self::createArrayDir($paths),
                'paths'     => $paths
            ),
            array('form' => false)
        );

        // Form found in several different places
        // --- START ---
        $paths     = $origpaths;
        $paths[0] .= '/form.browse.xml';
        $data[] = array(
            array('name' => 'Foobars'),
            array(
                'form_name' => 'form.browse',
                'structure' => self::createArrayDir($paths),
                'paths'     => $paths
            ),
            array('form' => vfsStream::url('root/'.$paths[0]))
        );

        $paths     = $origpaths;
        $paths[1] .= '/form.browse.xml';
        $data[] = array(
            array('name' => 'Foobars'),
            array(
                'form_name' => 'form.browse',
                'structure' => self::createArrayDir($paths),
                'paths'     => $paths
            ),
            array('form' => vfsStream::url('root/'.$paths[1]))
        );

        $paths     = $origpaths;
        $paths[2] .= '/form.browse.xml';
        $data[] = array(
            array('name' => 'Foobars'),
            array(
                'form_name' => 'form.browse',
                'structure' => self::createArrayDir($paths),
                'paths'     => $paths
            ),
            array('form' => vfsStream::url('root/'.$paths[2]))
        );

        $paths     = $origpaths;
        $paths[3] .= '/form.browse.xml';
        $data[] = array(
            array('name' => 'Foobars'),
            array(
                'form_name' => 'form.browse',
                'structure' => self::createArrayDir($paths),
                'paths'     => $paths
            ),
            array('form' => vfsStream::url('root/'.$paths[3]))
        );

        $paths     = $origpaths;
        $paths[4] .= '/form.browse.xml';
        $data[] = array(
            array('name' => 'Foobars'),
            array(
                'form_name' => 'form.browse',
                'structure' => self::createArrayDir($paths),
                'paths'     => $paths
            ),
            array('form' => vfsStream::url('root/'.$paths[4]))
        );

        $paths     = $origpaths;
        $paths[5] .= '/form.browse.xml';
        $data[] = array(
            array('name' => 'Foobars'),
            array(
                'form_name' => 'form.browse',
                'structure' => self::createArrayDir($paths),
                'paths'     => $paths
            ),
            array('form' => vfsStream::url('root/'.$paths[5]))
        );

        $paths     = $origpaths;
        $paths[6] .= '/form.browse.xml';
        $data[] = array(
            array('name' => 'Foobars'),
            array(
                'form_name' => 'form.browse',
                'structure' => self::createArrayDir($paths),
                'paths'     => $paths
            ),
            array('form' => vfsStream::url('root/'.$paths[6]))
        );

        $paths     = $origpaths;
        $paths[7] .= '/form.browse.xml';
        $data[] = array(
            array('name' => 'Foobars'),
            array(
                'form_name' => 'form.browse',
                'structure' => self::createArrayDir($paths),
                'paths'     => $paths
            ),
            array('form' => vfsStream::url('root/'.$paths[7]))
        );
        // --- END ---

        // Form with specific joomla suffix
        $paths     = $origpaths;
        $paths[2] .= '/form.browse.j32.xml';
        $data[] = array(
            array('name' => 'Foobars'),
            array(
                'form_name' => 'form.browse',
                'structure' => self::createArrayDir($paths),
                'paths'     => $paths,
                'suffix'    => array('.j32', '.j3')
            ),
            array('form' => vfsStream::url('root/'.$paths[2]))
        );

        $paths     = $origpaths;
        $paths[2] .= '/form.browse.j3.xml';
        $data[] = array(
            array('name' => 'Foobars'),
            array(
                'form_name' => 'form.browse',
                'structure' => self::createArrayDir($paths),
                'paths'     => $paths,
                'suffix'    => array('.j32', '.j3')
            ),
            array('form' => vfsStream::url('root/'.$paths[2]))
        );


        return $data;
    }

    public static function getTestLoadFormData()
    {
        $data[] = array(
            array('data' => array(1, 2, 3)),
            array('data' => array(1, 2, 3)),
        );

        $data[] = array(
            array('data' => array()),
            array('data' => array()),
        );

        $data[] = array(
            array('data' => false),
            array('data' => array()),
        );

        $data[] = array(
            array('data' => null),
            array('data' => array()),
        );

        return $data;
    }

    public static function getTestPreprocessForm()
    {
        $data[] = array(
            array(
                'runPlugin'      => array(true),
                'throwException' => false
            )
        );

        $data[] = array(
            array(
                'runPlugin'      => array(0, true, ''),
                'throwException' => false
            )
        );

        $data[] = array(
            array(
                'runPlugin'      => array(false),
                'throwException' => true
            )
        );

        $data[] = array(
            array(
                'runPlugin'      => array(true, false, false),
                'throwException' => true
            )
        );

        return $data;
    }

    public static function getTestValidateForm()
    {
        // Everything ok
        $data[] = array(
            array(
                'data'       => array('first' => 1, 'second' => 2),
                'group'      => 'dummy',
                'filterData' => array('first' => 2, 'second' => 1),
                'validate'   => true,
                'getErrors'  => '',
            ),
            array(
                'data'       => array('first' => 1, 'second' => 2),
                'group'      => 'dummy',
                'filterData' => array('first' => 2, 'second' => 1),
                'errMsg'     => '',
                'return'     => array('first' => 2, 'second' => 1)
            )
        );

        // Validation returns an exception
        $data[] = array(
            array(
                'data'       => array('first' => 1, 'second' => 2),
                'group'      => 'dummy',
                'filterData' => array('first' => 2, 'second' => 1),
                'validate'   => new Exception('Test exception'),
                'getErrors'  => '',
            ),
            array(
                'data'       => array('first' => 1, 'second' => 2),
                'group'      => 'dummy',
                'filterData' => array('first' => 2, 'second' => 1),
                'errMsg'     => 'Test exception',
                'return'     => false
            )
        );

        // Validation returns false (form sets no exception)
        $data[] = array(
            array(
                'data'       => array('first' => 1, 'second' => 2),
                'group'      => 'dummy',
                'filterData' => array('first' => 2, 'second' => 1),
                'validate'   => false,
                'getErrors'  => array('Test exception'),
            ),
            array(
                'data'       => array('first' => 1, 'second' => 2),
                'group'      => 'dummy',
                'filterData' => array('first' => 2, 'second' => 1),
                'errMsg'     => 'Test exception',
                'return'     => false
            )
        );

        // Validation returns false (form sets an exception)
        $data[] = array(
            array(
                'data'       => array('first' => 1, 'second' => 2),
                'group'      => 'dummy',
                'filterData' => array('first' => 2, 'second' => 1),
                'validate'   => false,
                'getErrors'  => array(new Exception('Test exception')),
            ),
            array(
                'data'       => array('first' => 1, 'second' => 2),
                'group'      => 'dummy',
                'filterData' => array('first' => 2, 'second' => 1),
                'errMsg'     => 'Test exception',
                'return'     => false
            )
        );

        return $data;
    }

    protected static function createArrayDir($paths)
    {
        $tree = array();
        foreach ($paths as $path) {
            $pathParts = explode('/', $path);
            $subTree   = array(array_pop($pathParts));

            if(strpos($subTree[0], '.') !== false)
            {
                $subTree = array($subTree[0] => '');
            }

            foreach (array_reverse($pathParts) as $dir) {
                $subTree = array($dir => $subTree);
            }
            $tree = array_merge_recursive($tree, $subTree);
        }

        return $tree;
    }
}