<?php

class TableFtest extends FOF30\Table\Table {

	public function __construct($table, $key, &$db, $config = array())
	{
		parent::__construct($table, $key, $db, $config);

		$this->_tbl     = '#__foftest_foobars';
		$this->_tbl_key = 'foftest_foobar_id';
	}
}