<?php
/**
 * @package	    FrameworkOnFramework.UnitTest
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

use FOF30\Table\Table as FOFTable;
use FOF30\Input\Input as FOFInput;
use FOF30\Controller\Controller as FOFController;

require_once 'controllerDataprovider.php';

class FOFControllerTest extends FtestCaseDatabase
{
	private $_stashedServer = array();

    protected function setUp()
    {
        $loadDataset = true;
        $annotations = $this->getAnnotations();

        // Do I need a dataset for this set or not?
        if(isset($annotations['method']) && isset($annotations['method']['preventDataLoading']))
        {
            $loadDataset = false;
        }

		parent::setUp();

		// Force a JDocumentHTML instance
		$this->saveFactoryState();
		JFactory::$document = JDocument::getInstance('html');

		// Fake the server variables to get JURI working right
		global $_SERVER;
		$this->_stashedServer = $_SERVER;
		$_SERVER['HTTP_HOST'] = 'www.example.com';
		$_SERVER['REQUEST_URI'] = '/index.php?option=com_foobar';
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		// Fake the session
		JFactory::$session = $this->getMockSession();
		$application = JFactory::getApplication('site');

		// Joomla requires that we produce a template in the templates directory. So we'll cheat and provide
		// the system template which is in our environment for 3.2
		$template = (object)array(
			'template'		=> 'system',
		);
		$attribute = new ReflectionProperty($application, 'template');
		$attribute->setAccessible(TRUE);
		$attribute->setValue($application, $template);

		// Replace the FOF30\Platform\Platform with our fake one
		$this->saveFOFPlatform();
		$this->replaceFOFPlatform();

        parent::setUp($loadDataset);

        FOFTable::forceInstance(null);
    }

	protected function tearDown()
	{
		// Restore the JFactory
		$this->restoreFactoryState();

		// Restore the FOF30\Platform\Platform object instance
		$this->restoreFOFPlatform();

		// Restore the $_SERVER global
		global $_SERVER;
		$_SERVER = $this->_stashedServer;

		// Call the parent
		parent::tearDown();
	}

    /**
     * @group           FOFController
     * @group           controllerCreateFilename
     * @covers          FOFController::createFilename
     * @dataProvider    getTestCreateFilename
     *
     * @preventDataLoading
     */
    public function testCreateFilename($test, $check)
    {
        $method = new ReflectionMethod('\\FOF30\\Controller\\Controller', 'createFilename');
        $method->setAccessible(true);
        $filename = $method->invoke(null, $test['type'], $test['parts']);

        $this->assertEquals($check['filename'], $filename, 'FOF30\Controller\Controller::createFilename created the wrong filename');
    }

    /**
     * @group           FOFController
     * @group           controllerBrowse
     * @covers          FOFController::browse
     * @dataProvider    getTestBrowse
     *
     * @preventDataLoading
     */
    public function testBrowse($test, $check)
    {
        $controller = $this->getMock('FOF30\Controller\Controller', array('display', 'getModel'));
        $controller->expects($this->any())->method('display')->with($this->equalTo($check['cache']));

        $taskCache = new ReflectionProperty($controller, 'cacheableTasks');
        $taskCache->setAccessible(true);
        $taskCache->setValue($controller, $test['cache']);

        $layout = new ReflectionProperty($controller, 'layout');
        $layout->setAccessible(true);
        $layout->setValue($controller, $test['layout']);

        $model      = $this->getMock('\FOF30\Model\Model', array('setState'));
        $model->expects($this->any())->method('setState')->with($this->equalTo('form_name'), $this->equalTo($check['form_name']));

        $controller->expects($this->any())->method('getModel')->will($this->returnValue($model));

        $return = $controller->browse();

        $this->assertEquals($check['return'], $return, 'FOF30\Controller\Controller::browse returned the wrong value');
    }

    /**
     * @group           FOFController
     * @group           controllerRead
     * @covers          FOFController::read
     * @dataProvider    getTestRead
     */
    public function testRead($test, $check)
    {
        $controller = $this->getMock('FOF30\Controller\Controller', array('display', 'getModel'));
        $controller->expects($this->any())->method('display')->with($this->equalTo($check['cache']));

        $taskCache = new ReflectionProperty($controller, 'cacheableTasks');
        $taskCache->setAccessible(true);
        $taskCache->setValue($controller, $test['cache']);

        $layout = new ReflectionProperty($controller, 'layout');
        $layout->setAccessible(true);
        $layout->setValue($controller, $test['layout']);

        // I have to load the record here since in the dataprovider the table is not populated yet
        if($test['loadid'])
        {
            $test['item']->load($test['loadid']);
        }

        $model      = $this->getMock('\FOF30\Model\Model', array('setState', 'getId', 'getItem'));
        $model->expects($this->any())->method('getId')->will($this->returnValue($test['id']));
        $model->expects($this->any())->method('getItem')->will($this->returnValue($test['item']));
        $model->expects($this->any())->method('setState')->with($this->equalTo('form_name'), $this->equalTo($check['form_name']));

        $controller->expects($this->any())->method('getModel')->will($this->returnValue($model));

        $return = $controller->read();

        $this->assertEquals($check['return'], $return, 'FOF30\Controller\Controller::read returned the wrong value');
    }

    /**
     * @group           FOFController
     * @group           controllerAdd
     * @covers          FOFController::add
     * @dataProvider    getTestAdd
     *
     * @preventDataLoading
     */
    public function testAdd($test, $check)
    {
        $controller = $this->getMock('FOF30\Controller\Controller', array('display', 'getModel'));
        $controller->expects($this->any())->method('display')->with($this->equalTo($check['cache']));

        $taskCache = new ReflectionProperty($controller, 'cacheableTasks');
        $taskCache->setAccessible(true);
        $taskCache->setValue($controller, $test['cache']);

        $layout = new ReflectionProperty($controller, 'layout');
        $layout->setAccessible(true);
        $layout->setValue($controller, $test['layout']);


        $model      = $this->getMock('FOF30\Model\Model', array('setState', 'getItem'));
        $model->expects($this->any())->method('getItem')->will($this->returnValue($test['item']));
        $model->expects($this->any())->method('setState')->with($this->equalTo('form_name'), $this->equalTo($check['form_name']));

        $controller->expects($this->any())->method('getModel')->will($this->returnValue($model));

        $return = $controller->add();

        $this->assertEquals($check['return'], $return, 'FOF30\Controller\Controller::add returned the wrong value');
    }

    /**
     * @group           FOFController
     * @group           controllerEdit
     * @covers          FOFController::edit
     * @dataProvider    getTestEdit
     */
    public function testEdit($test, $check)
    {
        $config = array(
            'input' => new FOFInput(array(
                    'option'    => 'com_foftest',
                    'view'      => 'foobar',
                    'returnurl' => $test['returnurl']
                ))
        );

        $controller = $this->getMock('FOF30\Controller\Controller', array('display', 'getModel', 'setRedirect'), array($config));
        $controller->expects($this->any())->method('display')->with($this->equalTo($check['cache']));

        if($test['checkout'])
        {
            $controller->expects($this->never())->method('setRedirect');
        }
        else
        {
            $controller->expects($this->once())->method('setRedirect')->with(
                $this->equalTo($check['returnUrl']),
                $this->equalTo(''),
                $this->equalTo('error')
            );
        }

        $taskCache = new ReflectionProperty($controller, 'cacheableTasks');
        $taskCache->setAccessible(true);
        $taskCache->setValue($controller, $test['cache']);

        $layout = new ReflectionProperty($controller, 'layout');
        $layout->setAccessible(true);
        $layout->setValue($controller, $test['layout']);

        // I have to load the record here since in the dataprovider the table is not populated yet
        if($test['loadid'])
        {
            $test['item']->load($test['loadid']);
        }

        $model      = $this->getMock('FOF30\Model\Model', array('setState', 'getId', 'getItem', 'checkout'));
        $model->expects($this->any())->method('getId')->will($this->returnValue($test['id']));
        $model->expects($this->any())->method('getItem')->will($this->returnValue($test['item']));
        $model->expects($this->any())->method('checkout')->will($this->returnValue($test['checkout']));
        $model->expects($this->any())->method('setState')->with($this->equalTo('form_name'), $this->equalTo($check['form_name']));

        $controller->expects($this->any())->method('getModel')->will($this->returnValue($model));

        $return = $controller->edit();

        $this->assertEquals($check['return'], $return, 'FOF30\Controller\Controller::edit returned the wrong value');
    }

    /**
     * @group           FOFController
     * @group           controllerCopy
     * @covers          FOFController::copy
     * @dataProvider    getTestCopy
     *
     * @preventDataLoading
     */
    public function testCopy($test, $check)
    {
        $config = array(
            'input' => new FOFInput(array(
                    'option'    => 'com_foftest',
                    'view'      => 'foobar',
                    'returnurl' => $test['returnurl']
                ))
        );

        $controller = $this->getMock('\\FOF30\\Controller\\Controller', array('getModel', 'setRedirect', '_csrfProtection'), array($config));
        $controller->expects($this->any())->method('_csrfProtection')->will($this->returnValue(null));

        if($test['copy'])
        {
            $controller->expects($this->once())->method('setRedirect')->with(
                $this->equalTo($check['returnUrl'])
            );
        }
        else
        {
            $controller->expects($this->once())->method('setRedirect')->with(
                $this->equalTo($check['returnUrl']),
                $this->equalTo(''),
                $this->equalTo('error')
            );
        }

        $model = $this->getMock('FOF30\Model\Model', array('getId', 'copy'));
        $model->expects($this->any())->method('getId')->will($this->returnValue(true));
        $model->expects($this->any())->method('copy')->will($this->returnValue($test['copy']));

        $controller->expects($this->any())->method('getModel')->will($this->returnValue($model));

        $return = $controller->copy();

        $this->assertEquals($check['return'], $return, 'FOF30\Controller\Controller::copy returned the wrong value');
    }

    /**
     * @group           FOFController
     * @group           controllerCancel
     * @covers          FOFController::cancel
     * @dataProvider    getTestCancel
     *
     * @preventDataLoading
     */
    public function testCancel($test, $check)
    {
        $config = array(
            'input' => new FOFInput(array(
                    'option'    => 'com_foftest',
                    'view'      => 'foobar',
                    'returnurl' => $test['returnurl']
                ))
        );

        $hackedSession = new JSession;

        // Manually set the session as active
        $property = new ReflectionProperty($hackedSession, '_state');
        $property->setAccessible(true);
        $property->setValue($hackedSession, 'active');

        $session = serialize(array('foftest_foobar_id' => 2, 'title' => 'Title from session'));

        // We're in CLI and no $_SESSION variable? No problem, I'll manually create it!
        // I'm going to hell for doing this...
        $_SESSION['__default']['com_foftest.foobars.savedata'] = $session;

        JFactory::$session = $hackedSession;

        $controller = $this->getMock('FOF30\Controller\Controller', array('getModel', 'setRedirect'), array($config));
        $controller->expects($this->any())->method('_csrfProtection')->will($this->returnValue(null));

        $controller->expects($this->once())->method('setRedirect')->with(
            $this->equalTo($check['returnUrl'])
        );

        $model = $this->getMock('FOF30\Model\Model', array('getId', 'copy'), array($config));
        $model->expects($this->any())->method('getId')->will($this->returnValue(true));
        $model->expects($this->any())->method('checkin')->will($this->returnValue($test['checkin']));

        $controller->expects($this->any())->method('getModel')->will($this->returnValue($model));

        $return = $controller->cancel();

        $this->assertEquals($check['return'], $return, 'FOF30\Controller\Controller::cancel returned the wrong value');

        $this->assertArrayNotHasKey('com_foftest.foobars.savedata', $_SESSION['__default'], 'FOF30\Controller\Controller::cancel should wipe saved session data');

        // Let's remove any evidence...
        unset($_SESSION);
    }


    /**
     * @group           FOFController
     * @group           controllerOrderdown
     * @covers          FOFController::orderdown
     * @dataProvider    getTestOrderDown
     *
     * @preventDataLoading
     */
    public function testOrderdown($test, $check)
    {
        $config = array(
            'input' => new FOFInput(array(
                    'option'    => 'com_foftest',
                    'view'      => 'foobar',
                    'returnurl' => $test['returnurl']
                ))
        );

        $controller = $this->getMock('FOF30\Controller\Controller', array('getModel', 'setRedirect', '_csrfProtection'), array($config));
        $controller->expects($this->any())->method('_csrfProtection')->will($this->returnValue(null));

        if($test['move'])
        {
            $controller->expects($this->once())->method('setRedirect')->with(
                $this->equalTo($check['returnUrl'])
            );
        }
        else
        {
            $controller->expects($this->once())->method('setRedirect')->with(
                $this->equalTo($check['returnUrl']),
                $this->equalTo(''),
                $this->equalTo('error')
            );
        }

        $model = $this->getMock('FOF30\Model\Model', array('getId', 'move'));
        $model->expects($this->any())->method('getId')->will($this->returnValue(true));
        $model->expects($this->any())->method('move')->will($this->returnValue($test['move']));

        $controller->expects($this->any())->method('getModel')->will($this->returnValue($model));

        $return = $controller->orderdown();

        $this->assertEquals($check['return'], $return, 'FOF30\Controller\Controller::orderdown returned the wrong value');
    }

    /**
     * @group           FOFController
     * @group           controllerOrderup
     * @covers          FOFController::orderup
     * @dataProvider    getTestOrderUp
     *
     * @preventDataLoading
     */
    public function testOrderup($test, $check)
    {
        $config = array(
            'input' => new FOFInput(array(
                    'option'    => 'com_foftest',
                    'view'      => 'foobar',
                    'returnurl' => $test['returnurl']
                ))
        );

        $controller = $this->getMock('FOF30\Controller\Controller', array('getModel', 'setRedirect', '_csrfProtection'), array($config));
        $controller->expects($this->any())->method('_csrfProtection')->will($this->returnValue(null));

        if($test['move'])
        {
            $controller->expects($this->once())->method('setRedirect')->with(
                $this->equalTo($check['returnUrl'])
            );
        }
        else
        {
            $controller->expects($this->once())->method('setRedirect')->with(
                $this->equalTo($check['returnUrl']),
                $this->equalTo(''),
                $this->equalTo('error')
            );
        }

        $model = $this->getMock('FOF30\Model\Model', array('getId', 'move'));
        $model->expects($this->any())->method('getId')->will($this->returnValue(true));
        $model->expects($this->any())->method('move')->will($this->returnValue($test['move']));

        $controller->expects($this->any())->method('getModel')->will($this->returnValue($model));

        $return = $controller->orderup();

        $this->assertEquals($check['return'], $return, 'FOF30\Controller\Controller::orderdup returned the wrong value');
    }

    /**
     * @group           FOFController
     * @group           controllerRemove
     * @covers          FOFController::remove
     * @dataProvider    getTestRemove
     *
     * @preventDataLoading
     */
    public function testRemove($test, $check)
    {
        $config = array(
            'input' => new FOFInput(array(
                    'option'    => 'com_foftest',
                    'view'      => 'foobar',
                    'returnurl' => $test['returnurl']
                ))
        );

        $controller = $this->getMock('FOF30\Controller\Controller', array('getModel', 'setRedirect', '_csrfProtection'), array($config));
        $controller->expects($this->any())->method('_csrfProtection')->will($this->returnValue(null));

        if($test['remove'])
        {
            $controller->expects($this->once())->method('setRedirect')->with(
                $this->equalTo($check['returnUrl'])
            );
        }
        else
        {
            $controller->expects($this->once())->method('setRedirect')->with(
                $this->equalTo($check['returnUrl']),
                $this->equalTo(''),
                $this->equalTo('error')
            );
        }

        $model = $this->getMock('FOF30\Model\Model', array('getId', 'delete'));
        $model->expects($this->any())->method('getId')->will($this->returnValue(true));
        $model->expects($this->any())->method('delete')->will($this->returnValue($test['remove']));

        $controller->expects($this->any())->method('getModel')->will($this->returnValue($model));

        $return = $controller->remove();

        $this->assertEquals($check['return'], $return, 'FOF30\Controller\Controller::remove returned the wrong value');
    }

	/**
	 * @group           FOFController
	 * @group           controllerSetRedirect
	 * @covers          FOFController::setRedirect
	 * @dataProvider    getTestSetRedirect
	 *
	 * @preventDataLoading
	 */
	public function testSetRedirect($test, $check)
	{
		$config = array(
			'autoRouting' => $test['route'],
			'input' => new FOFInput(array(
					'option'    => 'com_foftest',
					'view'      => 'foobar'
				))
		);

		$platform = $this->getMock('FOF30\Integration\Joomla\Platform', array('isBackend'));
		$platform->expects($this->any())->method('isBackend')->will($this->returnValue($test['backend']));

		\FOF30\Platform\Platform::forceInstance($platform);

		$controller = new FOFController($config);

		$type = new ReflectionProperty($controller, 'messageType');
		$type->setAccessible(true);

		if(isset($test['previousType']))
		{
			$type->setValue($controller, $test['previousType']);
		}

		$return = $controller->setRedirect($test['url'], $test['msg'], $test['type']);

		$this->assertInstanceOf('FOF30\Controller\Controller', $return, 'FOF30\Controller\Controller::setRedirect should return an instance of FOFController');

		$redirect = new ReflectionProperty($controller, 'redirect');
		$redirect->setAccessible(true);
		$this->assertEquals($check['redirect'], $redirect->getValue($controller), 'FOF30\Controller\Controller::setController created the wrong redirect URL');


		$this->assertEquals($check['type'], $type->getValue($controller), 'FOF30\Controller\Controller::setController set the wrong message type');

		$message = new ReflectionProperty($controller, 'message');
		$message->setAccessible(true);
		$this->assertEquals($check['message'], $message->getValue($controller), 'FOF30\Controller\Controller::setController set the wrong message');
	}

	/**
	 * @group           FOFController
	 * @group           controllerSetstate
	 * @covers          FOFController::setstate
	 * @dataProvider    getTestSetState
	 *
	 * @preventDataLoading
	 */
	public function testSetstate($test, $check)
	{
		$config = array(
			'input' => new FOFInput(array(
					'option'    => 'com_foftest',
					'view'      => 'foobar',
					'returnurl' => $test['returnurl']
				))
		);

		$controller = $this->getMock('FOF30\Controller\Controller', array('getModel', 'setRedirect'), array($config));

		if($test['publish'])
		{
			$controller->expects($this->once())->method('setRedirect')->with(
				$this->equalTo($check['returnUrl'])
			);
		}
		else
		{
			$controller->expects($this->once())->method('setRedirect')->with(
				$this->equalTo($check['returnUrl']),
				$this->equalTo(''),
				$this->equalTo('error')
			);
		}

		$model = $this->getMock('FOF30\Model\Model', array('getId', 'publish'));
		$model->expects($this->any())->method('getId')->will($this->returnValue(true));
		$model->expects($this->any())->method('publish')->will($this->returnValue($test['publish']));

		$controller->expects($this->any())->method('getModel')->will($this->returnValue($model));

		$setstate = new ReflectionMethod($controller, 'setstate');
		$setstate->setAccessible(true);

		$return = $setstate->invoke($controller, 0);

		$this->assertEquals($check['return'], $return, 'FOF30\Controller\Controller::setstate returned the wrong value');
	}

	/**
	 * @group           FOFController
	 * @group           controllerSetaccess
	 * @covers          FOFController::setaccess
	 * @dataProvider    getTestSetAccess
	 */
	public function testSetaccess($test, $check)
	{
		$config = array(
			'input' => new FOFInput(array(
					'option'    => 'com_foftest',
					'view'      => 'foobar',
					'returnurl' => $test['returnurl']
				))
		);

		$controller = $this->getMock('FOF30\Controller\Controller', array('getModel', 'setRedirect'), array($config));

		if($test['save'])
		{
			$controller->expects($this->once())->method('setRedirect')->with($this->equalTo($check['returnUrl']));
		}
		else
		{
			$controller->expects($this->once())->method('setRedirect')->with(
				$this->equalTo($check['returnUrl']),
				$this->equalTo(''),
				$this->equalTo('error')
			);
		}

		// I have to load the record here since in the dataprovider the table is not populated yet
		if($test['loadid'])
		{
			$test['item']->load($test['loadid']);
		}

		$model      = $this->getMock('FOF30\Model\Model', array('getId', 'getItem', 'save'));
		$model->expects($this->any())->method('getId')->will($this->returnValue($test['id']));
		$model->expects($this->any())->method('getItem')->will($this->returnValue($test['item']));
		$model->expects($this->any())->method('save')->will($this->returnValue($test['save']));

		$controller->expects($this->any())->method('getModel')->will($this->returnValue($model));

		$setaccess = new ReflectionMethod($controller, 'setaccess');
		$setaccess->setAccessible(true);

		$return = $setaccess->invoke($controller, $test['level']);

		$access = $test['item']->getColumnAlias('access');

		$this->assertEquals($check['return'], $return, 'FOF30\Controller\Controller::setaccess returned the wrong value');
		$this->assertEquals($check['level'], $test['item']->$access, 'FOF30\Controller\Controller::setaccess didn\'t set the access level to the table');
	}

	/**
	 * @group           FOFController
	 * @group           controllerGetModel
	 * @covers          FOFController::getModel
	 * @dataProvider    getTestGetModel
	 *
	 * @preventDataLoading
	 */
	public function testGetModel($test, $check)
	{
		$config = array(
			'input' => new FOFInput(array(
					'option'    => 'com_foftest',
					'view'      => 'foobar',
					'task'      => 'test'
				))
		);

        if(!$test['config'])
        {
            $check['config'] = $config;
        }

		$controller = $this->getMock('FOF30\Controller\Controller', array('createModel'), array($config));
        $task = new ReflectionProperty($controller, 'task');
        $task->setAccessible(true);
        $task->setValue($controller, 'test');

		if($test['model'])
		{
			$model = $this->getMock('FOF30\Model\Model', array('setState'));
			$model->expects($this->any())->method('setState')->with(
				$this->equalTo('task'),
				$this->equalTo('test')
			);
		}
		else
		{
			$model = false;
		}

		$controller->expects($this->any())->method('createModel')->will($this->returnValue($model));
        $controller->expects($this->any())->method('createModel')->with(
            $this->equalTo($check['name']),
            $this->equalTo($check['prefix']),
            $this->equalTo($check['config'])
        );

		$return = $controller->getModel($test['name'], $test['prefix'], $test['config']);

		if(!$check['return'])
		{
			$this->assertEquals($check['return'], $return, 'FOF30\Controller\Controller::getModel returned a wrong value');
		}
        else
        {
            $this->assertInstanceOf('FOF30\Model\Model', $return, 'FOF30\Controller\Controller::getModel returned a wrong value');
        }
	}


    /**
     * @group           FOFController
     * @group           controllerGetName
     * @covers          FOFController::getName
     * @dataProvider    getTestGetName
     *
     * @preventDataLoading
     */
    public function testGetName($test, $check)
    {
        //$controller = $this->getMock('FOF30\Controller\Controller', null, array(), $test['classname']);
        $controller = new FOFController();

        $name = new ReflectionProperty($controller, 'name');
        $name->setAccessible(true);
        $name->setValue($controller, $test['name']);

        $component = new ReflectionProperty($controller, 'bareComponent');
        $component->setAccessible(true);
        $component->setValue($controller, $test['component']);

        $controllerName = $controller->getName();

        $this->assertEquals($check['name'], $controllerName, 'FOF30\Controller\Controller::getName returned the wrong controller name');
    }

    /**
     * @group           FOFController
     * @group           controllerGetName
     * @covers          FOFController::getName
     *
     * @preventDataLoading
     */
    public function testGetNameException()
    {
        $this->setExpectedException('Exception');

        $controller = $this->getMock('FOF30\Controller\Controller', null, array(), 'WrongClassname');

        $name = new ReflectionProperty($controller, 'name');
        $name->setAccessible(true);
        $name->setValue($controller, '');

        $component = new ReflectionProperty($controller, 'bareComponent');
        $component->setAccessible(true);
        $component->setValue($controller, '');

        $controller->getName();
    }

    /**
     * @group           FOFController
     * @group           controllerGetView
     * @covers          FOFController::getView
     * @dataProvider    getTestGetView
     *
     * @preventDataLoading
     */
    public function testGetView($test, $check)
    {
        $config = array(
            'input' => new FOFInput(array(
                    'option'    => 'com_foftest',
                    'view'      => 'foobar'
                ))
        );

        $controller = $this->getMock('FOF30\Controller\Controller', array('createView'), array($config));

        if($test['cache'])
        {
            $controller->expects($this->never())->method('createView');

            $cache = new ReflectionProperty($controller, 'viewsCache');
            $cache->setAccessible(true);
            $cache->setValue($controller, array(
                md5($check['name'] . $check['type'] . $check['prefix'] . serialize($check['config'])) => 'cache'
            ));
        }
        else
        {
            $controller->expects($this->any())->method('createView')->will($this->returnValue(true));
            $controller->expects($this->any())->method('createView')->with(
                $this->equalTo($check['name']),
                $this->equalTo($check['prefix']),
                $this->equalTo($check['type']),
                $this->equalTo($check['config'])
            );
        }

        $return = $controller->getView($test['name'], $test['type'], $test['prefix'], $test['config']);

        $this->assertEquals($check['return'], $return, 'FOF30\Controller\Controller::getView returned a wrong value');
    }

    public function getTestCreateFilename()
    {
        return ControllerDataprovider::getTestCreateFilename();
    }

    public function getTestBrowse()
    {
        return ControllerDataprovider::getTestBrowse();
    }

    public function getTestRead()
    {
        return ControllerDataprovider::getTestRead();
    }

    public function getTestAdd()
    {
        return ControllerDataprovider::getTestAdd();
    }

    public function getTestEdit()
    {
        return ControllerDataprovider::getTestEdit();
    }

    public function getTestCopy()
    {
        return ControllerDataprovider::getTestCopy();
    }

    public function getTestCancel()
    {
        return ControllerDataprovider::getTestCancel();
    }

    public function getTestOrderDown()
    {
        return ControllerDataprovider::getTestOrderDown();
    }

    public function getTestOrderUp()
    {
        return ControllerDataprovider::getTestOrderUp();
    }

    public function getTestRemove()
    {
        return ControllerDataprovider::getTestRemove();
    }

	public function getTestSetRedirect()
	{
		return ControllerDataprovider::getTestSetRedirect();
	}

	public function getTestSetState()
	{
		return ControllerDataprovider::getTestSetState();
	}

	public function getTestSetAccess()
	{
		return ControllerDataprovider::getTestSetAccess();
	}

	public function getTestGetModel()
	{
		return ControllerDataprovider::getTestGetModel();
	}

    public function getTestGetName()
    {
        return ControllerDataprovider::getTestGetName();
    }

    public function getTestGetView()
    {
        return ControllerDataprovider::getTestGetView();
    }
}
