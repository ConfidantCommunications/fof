<?php
/**
 * @package	    FrameworkOnFramework.UnitTest
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

use org\bovigo\vfs\vfsStream;

/*
 * Since Joomla is using static calls to JToolbarHelper, it would be impossible to test. However we have created
 * a stub that will replace the original class and intercept all the function calls. In this way we can check if
 * a method was called and the arguments passed
 */
require_once 'toolbarHerlperStub.php';
require_once 'toolbarDataprovider.php';

class F0FToolbarTest extends FtestCase
{
    protected function setUp()
    {
        parent::setUp();

        F0FPlatform::forceInstance(null);
        JToolbarHelper::resetStack();
    }

    /**
     * @group           F0FToolbar
     * @group           toolbarRenderToolbar
     * @dataProvider    getTestRenderToolbar
     * @covers          F0FToolbar::renderToolbar
     */
    public function testRenderToolbar($test, $check)
    {
        $methods = array_merge($check['methods'], (array) $test['methods'], array('clearLinks'));

        if(isset($check['nomethods']))
        {
            $methods = array_merge($methods, (array) $check['nomethods']);
        }

        $config = array(
            'input' => new F0FInput()
        );

        $input = array(
            'option' => 'com_foftest',
            'tmpl'   => $test['tmpl'],
            'render_toolbar' => $test['render']
        );

        if(isset($test['config_input']))
        {
            $input = array_merge($input, $test['config_input']);
        }

        $config['input'] = new F0FInput($input);

        $toolbar = $this->getMock('F0FToolbar', $methods, array($config));

        // Let's check if the expected methods are really invoked
        foreach($check['methods'] as $method)
        {
            $toolbar->expects($this->once())->method($method);
        }

        // No check methods? It means that we want the execution to stop
        if(isset($check['nomethods']))
        {
            $toolbar->expects($this->never())->method($check['nomethods']);
        }

        $toolbar->renderToolbar($test['view'], $test['task'], $test['input']);
    }

    /**
     * @group           F0FToolbar
     * @dataProvider    getTestOnCpanelsBrowse
     * @covers          F0FToolbar::onCpanelsBrowse
     */
    public function testOnCpanelsBrowse($test, $check)
    {
        $platform = $this->getMock('F0FPlatform', array('isBackend'));
        $platform->expects($this->any())->method('isBackend')->will($this->returnValue($test['isBackend']));

        F0FPlatform::forceInstance($platform);

        $config = array(
            'renderFrontendSubmenu' => $test['submenu'],
            'renderFrontendButtons' => $test['buttons'],
            'input' => new F0FInput(array('option' => 'com_foftest'))
        );

        $toolbar = $this->getMock('F0FToolbar', array('renderSubmenu'), array($config));
        $toolbar->expects($this->any())->method('renderSubmenu')->will($this->returnValue(null));

        if($test['callSubmenu'])
        {
            $toolbar->expects($this->any())->method('renderSubmenu');
        }
        else
        {
            $toolbar->expects($this->never())->method('renderSubmenu');
        }

        $toolbar->onCpanelsBrowse();

        $invokedMethods = JToolbarHelper::getStack();

        $this->assertEquals($check['methods'], $invokedMethods, 'F0FToolbar::onCpanelsBrowse called the wrong methods');
    }

    /**
     * @group           F0FToolbar
     * @group           toolbarOnBrowse
     * @dataProvider    getTestOnBrowse
     * @covers          F0FToolbar::onBrowse
     */
    public function testOnBrowse($test, $check)
    {
        $platform = $this->getMock('F0FPlatform', array('isBackend'));
        $platform->expects($this->any())->method('isBackend')->will($this->returnValue($test['isBackend']));

        F0FPlatform::forceInstance($platform);

        $config = array(
            'renderFrontendSubmenu' => $test['submenu'],
            'renderFrontendButtons' => $test['buttons'],
            'input' => new F0FInput(array('option' => 'com_foftest', 'view' => $test['view']))
        );

        $toolbar = $this->getMock('F0FToolbar', array('renderSubmenu'), array($config));
        $toolbar->expects($this->any())->method('renderSubmenu')->will($this->returnValue(null));

        $toolbar->perms = (object)$test['perms'];

        if($test['callSubmenu'])
        {
            $toolbar->expects($this->any())->method('renderSubmenu');
        }
        else
        {
            $toolbar->expects($this->never())->method('renderSubmenu');
        }

        $toolbar->onBrowse();

        $invokedMethods = JToolbarHelper::getStack();

        $this->assertEquals($check['methods'], $invokedMethods, 'F0FToolbar::onBrowse called the wrong methods');
    }

    /**
     * @group           F0FToolbar
     * @group           toolbarOnRead
     * @dataProvider    getTestOnRead
     * @covers          F0FToolbar::onRead
     */
    public function testOnRead($test, $check)
    {
        $platform = $this->getMock('F0FPlatform', array('isBackend'));
        $platform->expects($this->any())->method('isBackend')->will($this->returnValue($test['isBackend']));

        F0FPlatform::forceInstance($platform);

        $config = array(
            'renderFrontendSubmenu' => $test['submenu'],
            'renderFrontendButtons' => $test['buttons'],
            'input' => new F0FInput(array('option' => 'com_foftest', 'view' => $test['view']))
        );

        $toolbar = $this->getMock('F0FToolbar', array('renderSubmenu'), array($config));
        $toolbar->expects($this->any())->method('renderSubmenu')->will($this->returnValue(null));

        if($test['callSubmenu'])
        {
            $toolbar->expects($this->any())->method('renderSubmenu');
        }
        else
        {
            $toolbar->expects($this->never())->method('renderSubmenu');
        }

        $toolbar->onRead();

        $invokedMethods = JToolbarHelper::getStack();

        $this->assertEquals($check['methods'], $invokedMethods, 'F0FToolbar::onRead called the wrong methods');
    }

    /**
     * @group           F0FToolbar
     * @group           toolbarOnAdd
     * @dataProvider    getTestOnAdd
     * @covers          F0FToolbar::onAdd
     */
    public function testOnAdd($test, $check)
    {
        $platform = $this->getMock('F0FPlatform', array('isBackend'));
        $platform->expects($this->any())->method('isBackend')->will($this->returnValue($test['isBackend']));

        F0FPlatform::forceInstance($platform);

        $config = array(
            'renderFrontendButtons' => $test['buttons'],
            'input' => new F0FInput(array('option' => 'com_foftest', 'view' => $test['view']))
        );

        $toolbar = new F0FToolbar($config);

        $toolbar->perms = (object)$test['perms'];

        $toolbar->onAdd();

        $invokedMethods = JToolbarHelper::getStack();

        $this->assertEquals($check['methods'], $invokedMethods, 'F0FToolbar::onAdd called the wrong methods');
    }

    /**
     * @group           F0FToolbar
     * @group           toolbarAppendLink
     * @dataProvider    getTestAppendLink
     * @covers          F0FToolbar::appendLink
     */
    public function testAppendLink($test, $check)
    {
        $config = array(
            'input' => new F0FInput(array('option' => 'com_foftest'))
        );

        $toolbar = new F0FToolbar($config);

        foreach($test['links'] as $link)
        {
            $toolbar->appendLink($link['name'], $link['link'], $link['active'], $link['icon'], $link['parent']);
        }

        $links = $toolbar->getLinks();

        $this->assertEquals($check['links'], $links, 'F0FToolbar::prefixLink failed to append the link');
    }

    /**
     * @group           F0FToolbar
     * @group           toolbarPrefixLink
     * @covers          F0FToolbar::prefixLink
     */
    public function testPrefixLink()
    {
        $config = array(
            'input' => new F0FInput(array('option' => 'com_foftest'))
        );

        $toolbar = new F0FToolbar($config);

        $toolbar->prefixLink('Cpanel', 'index.php?option=com_foftest&view=cpanel', true, null);
        $toolbar->prefixLink('Foobars', 'index.php?option=com_foftest&view=foobars', false, null);

        $links = $toolbar->getLinks();
        $check = array(
            '0' => array(
                'name'   => 'Foobars',
                'link'   => 'index.php?option=com_foftest&view=foobars',
                'active' => false,
                'icon'   => null
            ),
            '1' => array(
                'name'   => 'Cpanel',
                'link'   => 'index.php?option=com_foftest&view=cpanel',
                'active' => true,
                'icon'   => null
            )
        );

        $this->assertEquals($check, $links, 'F0FToolbar::prefixLink failed to prepend the link');

    }

    /**
     * @group           F0FToolbar
     * @group           toolbarRenderSubmenu
     * @dataProvider    getTestRenderSubmenu
     * @covers          F0FToolbar::renderSubmenu
     */
    public function testRenderSubmenu($test, $check)
    {
        $config = array(
            'input' => new F0FInput(array('option' => 'com_foftest', 'view' => $test['view']))
        );

        $toolbar = $this->getMock('F0FToolbar', array('getMyViews'), array($config));
        $toolbar->expects($this->any())->method('getMyViews')->will($this->returnValue($test['views']));

        $toolbar->renderSubmenu();

        $links = $toolbar->getLinks();

        $this->assertEquals($check['links'], $links, 'F0FToolbar::renderSubmenu created wrong submenu links');
    }

    /**
     * @group           F0FToolbar
     * @group           toolbarGetMyViews
     * @dataProvider    getTestGetMyViews
     * @covers          F0FToolbar::getMyViews
     */
    public function testGetMyViews($test, $check)
    {
        // First of all I stub the filesystem object, so it won't strip out the protocol part
        $filesystem = $this->getMock('F0FIntegrationJoomlaFilesystem', array('folderFolders', 'folderFiles'));
        $filesystem->expects($this->any())
                   ->method('folderFolders')
                   ->will($this->returnValue($test['folders']));

	    $filesystem->expects($this->any())
				   ->method('folderFiles')
				   ->will($this->returnCallback(function($path, $file){
			    // In theory, folderFiles should return a list of files, however inside the toolbar we're
			    // using it only to check if a specific file exists, so we can simply use is_file
			    $file = str_replace(array('^', '\\', '$'), '', $file);

			    if(is_file($path.'/'.$file)){
				    return array($file);
			    }
			    else{
				    return array();
			    }
		    }));

        $platform = $this->getMock('F0FPlatform', array('getComponentBaseDirs'));

        // Then I have to trick the platform, providing a template path
        $platform->expects($this->any())
                 ->method('getComponentBaseDirs')
                 ->will($this->returnValue(array(
		            'main' => vfsStream::url('root/administrator/components/com_foftest')
		        )
	        ));

        // Finally, force the platform to return my mocked object
        $platform->setFilesystemObject($filesystem);

        F0FPlatform::forceInstance($platform);

        vfsStream::setup('root', null, $test['structure']);

        $config = array(
            'input' => new F0FInput(array('option' => 'com_foftest'))
        );

        $toolbar = new F0FToolbar($config);
        $method  = new ReflectionMethod($toolbar, 'getMyViews');
        $method->setAccessible(true);

        $views = $method->invoke($toolbar);

	    $this->assertEquals($check['views'], $views, 'F0FToolbar::getMyViews returned a wrong list of views');
    }

    public function getTestRenderToolbar()
    {
        return ToolbarDataprovider::getTestRenderToolbar();
    }

    public function getTestOnCpanelsBrowse()
    {
        return ToolbarDataprovider::getTestOnCpanelsBrowse();
    }

    public function getTestOnBrowse()
    {
        return ToolbarDataprovider::getTestOnBrowse();
    }

    public function getTestOnRead()
    {
        return ToolbarDataprovider::getTestOnRead();
    }

    public function getTestOnAdd()
    {
        return ToolbarDataprovider::getTestOnAdd();
    }

    public function getTestAppendLink()
    {
        return ToolbarDataprovider::getTestAppendLink();
    }

    public function getTestRenderSubmenu()
    {
        return ToolbarDataprovider::getTestRenderSubmenu();
    }

    public function getTestGetMyViews()
    {
        return ToolbarDataprovider::getTestGetMyViews();
    }
}