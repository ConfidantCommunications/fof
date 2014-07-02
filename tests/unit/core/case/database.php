<?php

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/XmlDataSet.php';

use FOF30\Platform\Platform as FOFPlatform;

abstract class FtestCaseDatabase extends PHPUnit_Extensions_Database_TestCase
{
	public static   $database;
	public static   $dbo;

	protected       $loadDataset  = true;
	protected       $savedFactoryState = array ();

	public static function tearDownAfterClass()
	{
		JFactory::$database = self::$database;
	}

	/**
	 * Saves the Factory pointers
	 *
	 * @return void
	 */
	protected function saveFactoryState()
	{
		$this->savedFactoryState['application'] = JFactory::$application;
		$this->savedFactoryState['config'] 		= JFactory::$config;
		$this->savedFactoryState['session'] 	= JFactory::$session;
		$this->savedFactoryState['language'] 	= JFactory::$language;
		$this->savedFactoryState['document'] 	= JFactory::$document;
		$this->savedFactoryState['acl']	 		= JFactory::$acl;
		$this->savedFactoryState['database'] 	= JFactory::$database;
		$this->savedFactoryState['mailer']		= JFactory::$mailer;
	}

	/**
	 * Override of base setUp method, so we can flag which methods need to load a dataset
	 *
	 * @param bool $loadDataset
	 */
	protected function setUp($loadDataset = true)
	{
		$this->loadDataset = $loadDataset;

		if($this->loadDataset)
		{
			$this->databaseTester = NULL;

			$this->getDatabaseTester()->setSetUpOperation($this->getSetUpOperation());
			$this->getDatabaseTester()->setDataSet($this->getDataSet());
			$this->getDatabaseTester()->onSetUp();
		}

		$this->saveFactoryState();
	}

	/**
	 * Override of standard tearDown function. Connections with database are closed only if we
	 * requested a dataset for the current test
	 */
	protected function tearDown()
	{
		if($this->loadDataset)
		{
			$this->getDatabaseTester()->setTearDownOperation($this->getTearDownOperation());
			$this->getDatabaseTester()->setDataSet($this->getDataSet());
			$this->getDatabaseTester()->onTearDown();

			/**
			 * Destroy the tester after the test is run to keep DB connections
			 * from piling up.
			 */
			$this->databaseTester = NULL;
		}

		$this->restoreFactoryState();
	}

	/**
	 * Sets the Factory pointers
	 *
	 * @return void
	 */
	protected function restoreFactoryState()
	{
		JFactory::$application 	= $this->savedFactoryState['application'];
		JFactory::$config 		= $this->savedFactoryState['config'];
		JFactory::$session 		= $this->savedFactoryState['session'];
		JFactory::$language 	= $this->savedFactoryState['language'];
		JFactory::$document 	= $this->savedFactoryState['document'];
		JFactory::$acl 			= $this->savedFactoryState['acl'];
		JFactory::$database 	= $this->savedFactoryState['database'];
		JFactory::$mailer 		= $this->savedFactoryState['mailer'];
	}

	public static function setUpBeforeClass()
	{
		jimport('joomla.database.database');
		jimport('joomla.database.table');

		if (!is_object(self :: $dbo))
		{
			// Let's use the config file of our guinea pig
			require_once JPATH_BASE.'/configuration.php';

			$config = new JConfig();

			$options = array (
				'driver'	=> ((isset ($config)) && ($config->dbtype != 'mysqli')) ? $config->dbtype : 'mysql',
				'host' 		=> isset ($config) ? $config->host : '127.0.0.1',
				'user' 		=> isset ($config) ? $config->user : 'utuser',
				'password' 	=> isset ($config) ? $config->password : 'ut1234',
				'database' 	=> isset ($config) ? $config->db : 'joomla_ut',
				'prefix' 	=> isset ($config) ? $config->dbprefix : 'jos_'
			);

			try
			{
				self::$dbo = JDatabase::getInstance($options);
			}
			catch (RuntimeException $e)
			{
				define('DB_NOT_AVAILABLE', true);
			}

			if (class_exists('JError') && JError::isError(self::$dbo))
			{
				//ignore errors
				define('DB_NOT_AVAILABLE', true);
			}
		}

		self::$database 	= JFactory::$database;
		JFactory::$database = self::$dbo;
	}

	/**
	 * Sets the connection to the database
	 *
	 * @return PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
	 */
	protected function getConnection()
	{
		// Let's use the config file of our guinea pig
		require_once JPATH_BASE.'/configuration.php';

		$config = new JConfig();

		// P.A. Test database prefix is fixed with jos_ so we can setup common tables
		$options = array (
			'driver'	=> ((isset ($config)) && ($config->dbtype != 'mysqli')) ? $config->dbtype : 'mysql',
			'host' 		=> isset ($config) ? $config->host : '127.0.0.1',
			'user' 		=> isset ($config) ? $config->user : 'utuser',
			'password' 	=> isset ($config) ? $config->password : 'ut1234',
			'database' 	=> isset ($config) ? $config->db : 'joomla_ut',
			'prefix' 	=> 'jos_'
		);

		$pdo = new PDO($options['driver'].':host='.$options['host'].';dbname='.$options['database'], $options['user'], $options['password']);
		return $this->createDefaultDBConnection($pdo, $options['database']);
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_XmlDataSet XML dataset
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(JPATH_TESTS.'/unit/stubs/test.xml');
	}

	/**
	 * Gets a mock session object.
	 *
	 * @param   array  $options  An array of key-value options for the JSession mock.
	 * getId : the value to be returned by the mock getId method
	 * get.user.id : the value to assign to the user object id returned by get('user')
	 * get.user.name : the value to assign to the user object name returned by get('user')
	 * get.user.username : the value to assign to the user object username returned by get('user')
	 *
	 * @return  JSession
	 */
	public function getMockSession($options = array())
	{
		// Attempt to load the real class first.
		class_exists('JSession');

		return FtestMockSession::create($this, $options);
	}

	/**
	 * Assigns mock values to methods.
	 *
	 * @param   object  $mockObject  The mock object.
	 * @param   array   $array       An associative array of methods to mock with return values:<br />
	 * string (method name) => mixed (return value)
	 *
	 * @return  void
	 */
	public function assignMockReturns($mockObject, $array)
	{
		foreach ($array as $method => $return)
		{
			$mockObject->expects($this->any())
				->method($method)
				->will($this->returnValue($return));
		}
	}

	/**
	 * Assigns mock callbacks to methods.
	 * This method assumes that the mock callback is named {mock}{method name}.
	 *
	 * @param   object  $mockObject  The mock object that the callbacks are being assigned to.
	 * @param   array   $array       An array of methods names to mock with callbacks.
	 *
	 * @return  void
	 */
	public function assignMockCallbacks($mockObject, $array)
	{
		foreach ($array as $index => $method)
		{
			if (is_array($method))
			{
				$methodName	 = $index;
				$callback	 = $method;
			}
			else
			{
				$methodName	 = $method;
				$callback	 = array(get_called_class(), 'mock' . $method);
			}

			$mockObject->expects($this->any())
				->method($methodName)
				->will($this->returnCallback($callback));
		}
	}

	/**
	 * Save the current FOFPlatform object
	 *
	 * @return  void
	 */
	protected function saveFOFPlatform()
	{
		$this->_stashedFOFPlatform = clone FOFPlatform::getInstance();
	}

	/**
	 * Restore the saved FOFPlatform object
	 *
	 * @return  void
	 */
	protected function restoreFOFPlatform()
	{
		FOFPlatform::forceInstance($this->_stashedFOFPlatform);
	}

	/**
	 * Replace the FOFPlatform object with a slightly customised one which
	 * allows us to fake front-end, back-end and CLI execution at will.
	 */
	protected function replaceFOFPlatform()
	{
		$platform = new FtestPlatformJoomla();
		FOFPlatform::forceInstance($platform);
	}
}
