<?php
/**
 * @package	    FrameworkOnFramework.UnitTest
 * @subpackage  Dispatcher
 *
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once 'dispatcherDataprovider.php';

class FOFDispatcherTest extends FtestCase
{
    public function setUp()
    {
        parent::setUp();

		\FOF30\Platform\Platform::forceInstance(null);
    }

    /**
     * @group           F0FDispatcher
     * @group           dispatcherDispatch
     * @covers          \FOF30\Dispatcher\Dispatcher::dispatch
     * @dataProvider    getTestDispatch
     */
    public function testDispatch($test, $check)
    {
        $platform = $this->getMock('\\FOF30\\Platform\\Platform', array('isCli', 'raiseError', 'authorizeAdmin', 'setHeader'));
        $platform->expects($this->any())->method('isCli')->will($this->returnValue($test['isCli']));
        $platform->expects($this->any())->method('authorizeAdmin')->will($this->returnValue($test['auth']));

        $matcher = $check['result'] ? $this->never() : $this->once();
        $platform->expects($matcher)->method('raiseError');

        \FOF30\Platform\Platform::forceInstance($platform);

        $input = array_merge(array('option' => 'com_foftest'), $test['input']);

        $config = array(
            'input' => new \FOF30\Input\Input($input)
        );

        $dispatcher = $this->getMock('\\FOF30\\Dispatcher\\Dispatcher', array('onBeforeDispatch', 'onBeforeDispatchCLI', 'onAfterDispatch'), array($config));
        $dispatcher->expects($this->any())->method('onBeforeDispatch')->will($this->returnValue($test['before']));
        $dispatcher->expects($this->any())->method('onBeforeDispatchCLI')->will($this->returnValue($test['beforeCli']));
        $dispatcher->expects($this->any())->method('onAfterDispatch')->will($this->returnValue($test['after']));

        // I will ask to phpUnit to create a mock with a fixed name, in this way F0FController::getTmpInstance
        // will find the object and initialize it, using the mocked one
        // The only downside is that we can't controll it (eg stubbing and mocking)
        $view = \FOF30\Inflector\Inflector::pluralize($input['view']);
        $this->getMock('\\FOF30\\Controller\\Controller', array('execute'), array(), 'FoftestController'.ucfirst($view));

        $dispatcher->dispatch();
    }

    /**
     * @group           F0FDispatcher
     * @covers          \FOF30\Dispatcher\Dispatcher::onBeforeDispatch
     */
	public function testOnBeforeDispatch()
	{
		$dispatcher = \FOF30\Dispatcher\Dispatcher::getTmpInstance();

		$this->assertTrue($dispatcher->onBeforeDispatch(), 'onBeforeDispatch should return TRUE');
	}

    /**
     * @group           F0FDispatcher
     * @covers          \FOF30\Dispatcher\Dispatcher::onBeforeDispatchCLI
     */
	public function testOnBeforeDispatchCli()
	{
		$dispatcher = \FOF30\Dispatcher\Dispatcher::getTmpInstance();

		$this->assertTrue($dispatcher->onBeforeDispatchCLI(), 'onBeforeDispatchCLI should return TRUE');
	}

	/**
     * @group           F0FDispatcher
     * @group           dispatcherGetTak
     * @covers          \FOF30\Dispatcher\Dispatcher::getTask
	 * @dataProvider    getTestGetTask
	 */
	public function testGetTask($input, $view, $frontend, $method, $expected, $message)
	{
		$mockPlatform = $this->getMock('\\FOF30\\Platform\\Platform', array('isFrontend'));
		$mockPlatform->expects($this->any())
					 ->method('isFrontend')
					 ->will($this->returnValue($frontend));

		\FOF30\Platform\Platform::forceInstance($mockPlatform);

		$_SERVER['REQUEST_METHOD'] = $method;
		$dispatcher = \FOF30\Dispatcher\Dispatcher::getTmpInstance();
		$reflection = new ReflectionClass($dispatcher);

		$property = $reflection->getProperty('input');
		$property->setAccessible(true);

		$method  = $reflection->getMethod('getTask');
		$method->setAccessible(true);

		$property->setValue($dispatcher, $input);
		$task = $method->invokeArgs($dispatcher, array($view));
		$this->assertEquals($expected, $task, $message);
	}

    /**
     * @group           F0FDispatcher
     * @group           dispatcherTransparentAuthentication
     * @covers          \FOF30\Dispatcher\Dispatcher::transparentAuthentication
     * @dataProvider    getTestTransparentAuthentication
     */
    public function testTransparentAuthentication($test, $check)
    {
        $platform = $this->getMock('\\FOF30\\Platform\\Platform', array('getUser', 'loginUser'));
        $platform->expects($this->any())->method('getUser')->will($this->returnValue((object) array('guest' => $test['guest'])));

        if($check['login'])
        {
            $platform->expects($this->atLeastOnce())->method('loginUser')->will($this->returnValue(true));
        }
        else
        {
            $platform->expects($this->never())->method('loginUser');
        }

        \FOF30\Platform\Platform::forceInstance($platform);

        if(isset($test['server']))
        {
            $_SERVER = array_merge($_SERVER, $test['server']);
        }

        $input = array('format' => $test['format']);

        if(isset($test['input']))
        {
            $input = array_merge($input, $test['input']);
        }

        $config = array(
            'input' => new \FOF30\Input\Input($input)
        );

        $dispatcher = new \FOF30\Dispatcher\Dispatcher($config);

        if(isset($test['authKey']))
        {
            $property = new ReflectionProperty($dispatcher, 'fofAuth_Key');
            $property->setAccessible(true);
            $property->setValue($dispatcher, $test['authKey']);
        }

        $dispatcher->transparentAuthentication();
    }

    public function getTestDispatch()
    {
        return DispatcherDataprovider::getTestDispatch();
    }

    public function getTestGetTask()
    {
        return DispatcherDataprovider::getTestGetTask();
    }

    public function getTestTransparentAuthentication()
    {
        return DispatcherDataprovider::getTestTransparentAuthentication();
    }
}