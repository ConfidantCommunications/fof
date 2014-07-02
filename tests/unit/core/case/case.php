<?php
/**
 * @package    FrameworkOnFramework.UnitTest
 * @subpackage Core
 *
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

abstract class FtestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * @var			array	The JFactory pointers saved before the execution of the test
	 */
	protected $savedFactoryState = array();

	/**
	 * @var			FOF30\Platform\Platform   The stashed FOF30\Platform\Platform instance
	 */
	protected $_stashedFOFPlatform = null;

	/**
	 * @var         array  The list of errors expected to be encountered during the test.
	 */
	protected $expectedErrors;

	/**
	 * @var         array  JError handler state stashed away to be restored later.
	 */
	private $_stashedErrorState = array();

	/**
	 * Saves the Factory pointers
	 *
	 * @return void
	 */
	protected function saveFactoryState()
	{
		$this->savedFactoryState['application']	 = JFactory::$application;
		$this->savedFactoryState['config']		 = JFactory::$config;
		$this->savedFactoryState['dates']		 = JFactory::$dates;
		$this->savedFactoryState['session']		 = JFactory::$session;
		$this->savedFactoryState['language']	 = JFactory::$language;
		$this->savedFactoryState['document']	 = JFactory::$document;
		$this->savedFactoryState['acl']			 = JFactory::$acl;
		$this->savedFactoryState['database']	 = JFactory::$database;
		$this->savedFactoryState['mailer']		 = JFactory::$mailer;
	}

	/**
	 * Sets the Factory pointers
	 *
	 * @return  void
	 */
	protected function restoreFactoryState()
	{
		JFactory::$application	= $this->savedFactoryState['application'];
		JFactory::$config		= $this->savedFactoryState['config'];
		JFactory::$dates		= $this->savedFactoryState['dates'];
		JFactory::$session		= $this->savedFactoryState['session'];
		JFactory::$language		= $this->savedFactoryState['language'];
		JFactory::$document		= $this->savedFactoryState['document'];
		JFactory::$acl			= $this->savedFactoryState['acl'];
		JFactory::$database		= $this->savedFactoryState['database'];
		JFactory::$mailer		= $this->savedFactoryState['mailer'];
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
	 * Receives the callback from JError and logs the required error information for the test.
	 *
	 * @param   JException  $error  The JException object from JError
	 *
	 * @return  boolean  To not continue with JError processing
	 */
	public static function errorCallback($error)
	{
		return false;
	}

	/**
	 * Callback receives the error from JError and deals with it appropriately
	 * If a test expects a JError to be raised, it should call this setExpectedError first
	 * If you don't call this method first, the test will fail.
	 *
	 * @param   JException  $error  The JException object from JError
	 *
	 * @return  JException
	 */
	public function expectedErrorCallback($error)
	{
		foreach ($this->expectedErrors as $key => $err)
		{
			$thisError = true;

			foreach ($err as $prop => $value)
			{
				if ($error->get($prop) !== $value)
				{
					$thisError = false;
				}
			}

			if ($thisError)
			{
				unset($this->expectedErrors[$key]);

				return $error;
			}
		}

		$this->fail('An unexpected error occurred - ' . $error->get('message'));

		return $error;
	}

	/**
	 * Tells the unit tests that a method or action you are about to attempt
	 * is expected to result in JError::raiseSomething being called.
	 *
	 * If you don't call this method first, the test will fail.
	 * If you call this method during your test and the error does not occur, then your test
	 * will also fail because we assume you were testing to see that an error did occur when it was
	 * supposed to.
	 *
	 * If passed without argument, the array is initialized if it hsn't been already
	 *
	 * @param   mixed  $error  The JException object to expect.
	 *
	 * @return  void
	 */
	public function setExpectedError($error = null)
	{
		if (!is_array($this->expectedErrors))
		{
			$this->expectedErrors = array();

			// Handle optional usage of JError until removed.
			if (class_exists('JError'))
			{
				JError::setErrorHandling(E_NOTICE, 'callback', array($this, 'expectedErrorCallback'));
				JError::setErrorHandling(E_WARNING, 'callback', array($this, 'expectedErrorCallback'));
				JError::setErrorHandling(E_ERROR, 'callback', array($this, 'expectedErrorCallback'));
			}
		}

		if (!is_null($error))
		{
			$this->expectedErrors[] = $error;
		}
	}

	/**
	 * Sets the JError error handlers.
	 *
	 * @return  void
	 */
	protected function restoreErrorHandlers()
	{
		$this->setErrorhandlers($this->_stashedErrorState);
	}

	/**
	 * Saves the current state of the JError error handlers.
	 *
	 * @return  void
	 */
	protected function saveErrorHandlers()
	{
		$this->_stashedErrorState = array();

		// Handle optional usage of JError until removed.
		if (class_exists('JError'))
		{
			$this->_stashedErrorState[E_NOTICE]	 = JError::getErrorHandling(E_NOTICE);
			$this->_stashedErrorState[E_WARNING] = JError::getErrorHandling(E_WARNING);
			$this->_stashedErrorState[E_ERROR]	 = JError::getErrorHandling(E_ERROR);
		}
	}

	/**
	 * Sets the JError error handlers.
	 *
	 * @param   array  $errorHandlers  araay of values and options to set the handlers
	 *
	 * @return  void
	 */
	protected function setErrorHandlers($errorHandlers)
	{
		$mode	 = null;
		$options = null;

		foreach ($errorHandlers as $type => $params)
		{
			$mode = $params['mode'];

			// Handle optional usage of JError until removed.
			if (class_exists('JError'))
			{
				if (isset($params['options']))
				{
					JError::setErrorHandling($type, $mode, $params['options']);
				}
				else
				{
					JError::setErrorHandling($type, $mode);
				}
			}
		}
	}

	/**
	 * Sets the JError error handlers to callback mode and points them at the test logging method.
	 *
	 * @param   string  $testName  The name of the test class for which to set the error callback method.
	 *
	 * @return  void
	 */
	protected function setErrorCallback($testName)
	{
		$callbackHandlers = array(
			E_NOTICE	 => array('mode'		 => 'callback', 'options'	 => array($testName, 'errorCallback')),
			E_WARNING	 => array('mode'		 => 'callback', 'options'	 => array($testName, 'errorCallback')),
			E_ERROR		 => array('mode'		 => 'callback', 'options'	 => array($testName, 'errorCallback'))
		);

		$this->setErrorHandlers($callbackHandlers);
	}

	/**
	 * Save the current FOF30\Platform\Platform object
	 *
	 * @return  void
	 */
	protected function saveFOFPlatform()
	{
		$this->_stashedFOFPlatform = clone FOF30\Platform\Platform::getInstance();
	}

	/**
	 * Restore the saved FOFPlatform object
	 *
	 * @return  void
	 */
	protected function restoreFOFPlatform()
	{
		FOF30\Platform\Platform::forceInstance($this->_stashedFOFPlatform);
	}

	/**
	 * Replace the FOFPlatform object with a slightly customised one which
	 * allows us to fake front-end, back-end and CLI execution at will.
	 */
	protected function replaceFOFPlatform()
	{
		$platform = new FtestPlatformJoomla();
		FOF30\Platform\Platform::forceInstance($platform);
	}


	/**
	 * Overrides the parent setup method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp()
	{
		$this->setExpectedError();

		parent::setUp();
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 */
	protected function tearDown()
	{
		if (is_array($this->expectedErrors) && !empty($this->expectedErrors))
		{
			$this->fail('An expected error was not raised.');
		}

		// Handle optional usage of JError until removed.
		if (class_exists('JError'))
		{
			JError::setErrorHandling(E_NOTICE, 'ignore');
			JError::setErrorHandling(E_WARNING, 'ignore');
			JError::setErrorHandling(E_ERROR, 'ignore');
		}

		parent::tearDown();
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
}
