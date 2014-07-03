<?php

use FOF30\Table\Table as FOFTable;
use FOF30\Input\Input as FOFInput;

class ControllerDataprovider
{
    public static function getTestCreateFilename()
    {
        $data[] = array(
            array(
                'type'  => 'controller',
                'parts' => array(
                    'name' => 'foobar'
                )
            ),
            array(
                'filename' => 'foobar.php'
            )
        );

        $data[] = array(
            array(
                'type'  => 'controller',
                'parts' => array(
                    'name'   => 'foobar',
                    'format' => 'html'
                )
            ),
            array(
                'filename' => 'foobar.php'
            )
        );

        $data[] = array(
            array(
                'type'  => 'controller',
                'parts' => array(
                    'name'   => 'foobar',
                    'format' => 'raw'
                )
            ),
            array(
                'filename' => 'foobar.raw.php'
            )
        );

        $data[] = array(
            array(
                'type'  => 'view',
                'parts' => array(
                    'name' => 'foobar',
                    'type' => 'html'
                )
            ),
            array(
                'filename' => 'foobar/view.html.php'
            )
        );

        $data[] = array(
            array(
                'type'  => 'view',
                'parts' => array(
                    'name' => 'foobar',
                    'type' => 'raw'
                )
            ),
            array(
                'filename' => 'foobar/view.raw.php'
            )
        );

        return $data;
    }

    public static function getTestBrowse()
    {
        $data[] = array(
            array(
                'cache'  => array('browse', 'read'),
                'layout' => ''
            ),
            array(
                'cache'     => true,
                'form_name' => 'form.default',
                'return'    => true
            )
        );

        $data[] = array(
            array(
                'cache'  => array('browse', 'read'),
                'layout' => 'dummy'
            ),
            array(
                'cache'     => true,
                'form_name' => 'form.dummy',
                'return'    => true
            )
        );

        $data[] = array(
            array(
                'cache'  => array('read'),
                'layout' => ''
            ),
            array(
                'cache'     => false,
                'form_name' => 'form.default',
                'return'    => true
            )
        );

        return $data;
    }

    public static function getTestRead()
    {
        $item = FOFTable::getAnInstance('Foobar', 'FoftestTable');
        $data[] = array(
            array(
                'cache'  => array('browse', 'read'),
                'layout' => null,
                'id'     => 2,
                'item'   => $item,
                'loadid' => 2
            ),
            array(
                'cache'     => true,
                'form_name' => 'form.item',
                'return'    => true
            )
        );

        $data[] = array(
            array(
                'cache'  => array('browse', 'read'),
                'layout' => 'dummy',
                'id'     => 2,
                'item'   => $item,
                'loadid' => 2
            ),
            array(
                'cache'     => true,
                'form_name' => 'form.dummy',
                'return'    => true
            )
        );

        $data[] = array(
            array(
                'cache'  => array('browse'),
                'layout' => null,
                'id'     => 2,
                'item'   => $item,
                'loadid' => 2
            ),
            array(
                'cache'     => false,
                'form_name' => 'form.item',
                'return'    => true
            )
        );

        $data[] = array(
            array(
                'cache'  => array('browse', 'read'),
                'layout' => null,
                'id'     => 2,
                'item'   => $item,
                'loadid' => 3
            ),
            array(
                'cache'     => true,
                'form_name' => 'form.item',
                'return'    => false
            )
        );

        $data[] = array(
            array(
                'cache'  => array('browse', 'read'),
                'layout' => null,
                'id'     => 2,
                'item'   => new stdClass(),
                'loadid' => 0
            ),
            array(
                'cache'     => true,
                'form_name' => 'form.item',
                'return'    => false
            )
        );

        return $data;
    }

    public static function getTestAdd()
    {
        $item = FOFTable::getAnInstance('Foobar', 'FoftestTable');
        $data[] = array(
            array(
                'cache'  => array('browse', 'read'),
                'layout' => null,
                'item'   => $item,
            ),
            array(
                'cache'     => false,
                'form_name' => 'form.form',
                'return'    => false
            )
        );

        $data[] = array(
            array(
                'cache'  => array('browse', 'read'),
                'layout' => 'dummy',
                'item'   => $item,
            ),
            array(
                'cache'     => false,
                'form_name' => 'form.dummy',
                'return'    => false
            )
        );

        $data[] = array(
            array(
                'cache'  => array('browse', 'add'),
                'layout' => null,
                'item'   => $item
            ),
            array(
                'cache'     => true,
                'form_name' => 'form.form',
                'return'    => null
            )
        );

        $data[] = array(
            array(
                'cache'  => array('browse', 'read'),
                'layout' => null,
                'item'   => new stdClass(),
            ),
            array(
                'cache'     => true,
                'form_name' => 'form.form',
                'return'    => false
            )
        );

        return $data;
    }

    public static function getTestEdit()
    {
        $item = FOFTable::getAnInstance('Foobar', 'FoftestTable');
        $data[] = array(
            array(
                'cache'     => array('browse', 'read'),
                'layout'    => null,
                'checkout'  => true,
                'returnurl' => '',
                'id'        => 2,
                'item'      => $item,
                'loadid'    => 2
            ),
            array(
                'cache'     => false,
                'form_name' => 'form.form',
                'return'    => true
            )
        );

        $data[] = array(
            array(
                'cache'     => array('browse', 'read'),
                'layout'    => 'dummy',
                'checkout'  => true,
                'returnurl' => '',
                'id'        => 2,
                'item'      => $item,
                'loadid'    => 2
            ),
            array(
                'cache'     => false,
                'form_name' => 'form.dummy',
                'return'    => true
            )
        );

        $data[] = array(
            array(
                'cache'     => array('browse', 'read', 'edit'),
                'layout'    => null,
                'checkout'  => true,
                'returnurl' => '',
                'id'        => 2,
                'item'      => $item,
                'loadid'    => 2
            ),
            array(
                'cache'     => true,
                'form_name' => 'form.form',
                'return'    => true
            )
        );

        $data[] = array(
            array(
                'cache'     => array('browse', 'read'),
                'layout'    => null,
                'checkout'  => true,
                'returnurl' => '',
                'id'        => 2,
                'item'      => $item,
                'loadid'    => 3
            ),
            array(
                'cache'     => false,
                'form_name' => 'form.form',
                'return'    => false
            )
        );

        $data[] = array(
            array(
                'cache'     => array('browse', 'read'),
                'layout'    => null,
                'checkout'  => true,
                'returnurl' => '',
                'id'        => 2,
                'item'      => new stdClass(),
                'loadid'    => 0
            ),
            array(
                'cache'     => false,
                'form_name' => 'form.form',
                'return'    => false
            )
        );

        $data[] = array(
            array(
                'cache'     => array('browse', 'read'),
                'layout'    => null,
                'checkout'  => false,
                'returnurl' => '',
                'id'        => 2,
                'item'      => $item,
                'loadid'    => 2
            ),
            array(
                'cache'     => false,
                'form_name' => 'form.form',
                'return'    => false,
                'returnUrl' => 'index.php?option=com_foftest&view=foobars'
            )
        );

        $data[] = array(
            array(
                'cache'     => array('browse', 'read'),
                'layout'    => null,
                'checkout'  => false,
                'returnurl' => base64_encode('index.php?option=com_foftest&view=returnurl'),
                'id'        => 2,
                'item'      => $item,
                'loadid'    => 2
            ),
            array(
                'cache'     => false,
                'form_name' => 'form.form',
                'return'    => false,
                'returnUrl' => 'index.php?option=com_foftest&view=returnurl'
            )
        );

        return $data;
    }

    public static function getTestCopy()
    {
        $data[] = array(
            array(
                'copy'      => true,
                'returnurl' => '',
            ),
            array(
                'return'    => true,
                'returnUrl' => 'index.php?option=com_foftest&view=foobars'
            )
        );

        $data[] = array(
            array(
                'copy'      => true,
                'returnurl' => base64_encode('index.php?option=com_foftest&view=returnurl'),
            ),
            array(
                'return'    => true,
                'returnUrl' => 'index.php?option=com_foftest&view=returnurl'
            )
        );

        $data[] = array(
            array(
                'copy'      => false,
                'returnurl' => base64_encode('index.php?option=com_foftest&view=returnurl'),
            ),
            array(
                'return'    => false,
                'returnUrl' => 'index.php?option=com_foftest&view=returnurl'
            )
        );

        return $data;
    }

    public static function getTestCancel()
    {
        $data[] = array(
            array(
                'checkin'   => true,
                'returnurl' => ''
            ),
            array(
                'return'    => true,
                'returnUrl' => 'index.php?option=com_foftest&view=foobars'
            )
        );

        $data[] = array(
            array(
                'checkin'   => false,
                'returnurl' => ''
            ),
            array(
                'return'    => true,
                'returnUrl' => 'index.php?option=com_foftest&view=foobars'
            )
        );

        $data[] = array(
            array(
                'checkin'   => false,
                'returnurl' => base64_encode('index.php?option=com_foftest&view=returnurl')
            ),
            array(
                'return'    => true,
                'returnUrl' => 'index.php?option=com_foftest&view=returnurl'
            )
        );

        return $data;
    }

    public static function getTestOrderDown()
    {
        $data[] = array(
            array(
                'move'   => true,
                'returnurl' => ''
            ),
            array(
                'return'    => true,
                'returnUrl' => 'index.php?option=com_foftest&view=foobars'
            )
        );

        $data[] = array(
            array(
                'move'   => false,
                'returnurl' => ''
            ),
            array(
                'return'    => false,
                'returnUrl' => 'index.php?option=com_foftest&view=foobars'
            )
        );

        $data[] = array(
            array(
                'move'   => true,
                'returnurl' => base64_encode('index.php?option=com_foftest&view=returnurl')
            ),
            array(
                'return'    => true,
                'returnUrl' => 'index.php?option=com_foftest&view=returnurl'
            )
        );

        return $data;
    }

    public static function getTestOrderUp()
    {
        $data[] = array(
            array(
                'move'   => true,
                'returnurl' => ''
            ),
            array(
                'return'    => true,
                'returnUrl' => 'index.php?option=com_foftest&view=foobars'
            )
        );

        $data[] = array(
            array(
                'move'   => false,
                'returnurl' => ''
            ),
            array(
                'return'    => false,
                'returnUrl' => 'index.php?option=com_foftest&view=foobars'
            )
        );

        $data[] = array(
            array(
                'move'   => true,
                'returnurl' => base64_encode('index.php?option=com_foftest&view=returnurl')
            ),
            array(
                'return'    => true,
                'returnUrl' => 'index.php?option=com_foftest&view=returnurl'
            )
        );

        return $data;
    }

    public static function getTestRemove()
    {
        $data[] = array(
            array(
                'remove'   => true,
                'returnurl' => ''
            ),
            array(
                'return'    => true,
                'returnUrl' => 'index.php?option=com_foftest&view=foobars'
            )
        );

        $data[] = array(
            array(
                'remove'   => false,
                'returnurl' => ''
            ),
            array(
                'return'    => false,
                'returnUrl' => 'index.php?option=com_foftest&view=foobars'
            )
        );

        $data[] = array(
            array(
                'remove'   => true,
                'returnurl' => base64_encode('index.php?option=com_foftest&view=returnurl')
            ),
            array(
                'return'    => true,
                'returnUrl' => 'index.php?option=com_foftest&view=returnurl'
            )
        );

        return $data;
    }

	public static function getTestSetRedirect()
	{
		// No routing at all
		$data[] = array(
			array(
				'route'     => 0,
				'backend'   => false,
				'url'       => 'index.php?option=com_foftest&view=foobar',
				'msg'       => '',
				'type'      => ''
			),
			array(
				'redirect' => 'index.php?option=com_foftest&view=foobar',
				'type'     => 'message',
				'message'  => ''
			)
		);

		// Fronten and I want routing on frontend
		$data[] = array(
			array(
				'route'     => 1,
				'backend'   => false,
				'url'       => 'index.php?option=com_foftest&view=foobar',
				'msg'       => '',
				'type'      => ''
			),
			array(
				'redirect' => '/index.php/component/foftest/?view=foobar',
				'type'     => 'message',
				'message'  => ''
			)
		);

		// Backend and I want routing on frontend
		$data[] = array(
			array(
				'route'     => 1,
				'backend'   => true,
				'url'       => 'index.php?option=com_foftest&view=foobar',
				'msg'       => '',
				'type'      => ''
			),
			array(
				'redirect' => 'index.php?option=com_foftest&view=foobar',
				'type'     => 'message',
				'message'  => ''
			)
		);

		// Frontend and I want routing on backend
		$data[] = array(
			array(
				'route'     => 2,
				'backend'   => false,
				'url'       => 'index.php?option=com_foftest&view=foobar',
				'msg'       => '',
				'type'      => ''
			),
			array(
				'redirect' => 'index.php?option=com_foftest&view=foobar',
				'type'     => 'message',
				'message'  => ''
			)
		);

		// Backend and I want routing on backend
		$data[] = array(
			array(
				'route'     => 2,
				'backend'   => true,
				'url'       => 'index.php?option=com_foftest&view=foobar',
				'msg'       => '',
				'type'      => ''
			),
			array(
				'redirect' => '/index.php/component/foftest/?view=foobar',
				'type'     => 'message',
				'message'  => ''
			)
		);

		// Backend and I always want routing
		$data[] = array(
			array(
				'route'     => 3,
				'backend'   => false,
				'url'       => 'index.php?option=com_foftest&view=foobar',
				'msg'       => '',
				'type'      => ''
			),
			array(
				'redirect' => '/index.php/component/foftest/?view=foobar',
				'type'     => 'message',
				'message'  => ''
			)
		);

		// Frontend and I always want routing
		$data[] = array(
			array(
				'route'     => 3,
				'backend'   => true,
				'url'       => 'index.php?option=com_foftest&view=foobar',
				'msg'       => '',
				'type'      => ''
			),
			array(
				'redirect' => '/index.php/component/foftest/?view=foobar',
				'type'     => 'message',
				'message'  => ''
			)
		);

		// SEF url should not be routed
		$data[] = array(
			array(
				'route'     => 3,
				'backend'   => false,
				'url'       => 'already-sef-url',
				'msg'       => '',
				'type'      => ''
			),
			array(
				'redirect' => 'already-sef-url',
				'type'     => 'message',
				'message'  => ''
			)
		);

		// Check for message and type
		$data[] = array(
			array(
				'route'     => 0,
				'backend'   => false,
				'url'       => 'index.php?option=com_foftest&view=foobar',
				'msg'       => 'Test message',
				'type'      => 'error'
			),
			array(
				'redirect' => 'index.php?option=com_foftest&view=foobar',
				'type'     => 'error',
				'message'  => 'Test message'
			)
		);

		// Empty type, but I already had a type previously set
		$data[] = array(
			array(
				'route'     => 0,
				'backend'   => false,
				'url'       => 'index.php?option=com_foftest&view=foobar',
				'msg'       => 'Test message',
				'type'      => '',
				'previousType' => 'notice'
			),
			array(
				'redirect' => 'index.php?option=com_foftest&view=foobar',
				'type'     => 'notice',
				'message'  => 'Test message'
			)
		);

		return $data;
	}

	public static function getTestSetState()
	{
		$data[] = array(
			array(
				'publish'   => true,
				'returnurl' => ''
			),
			array(
				'return'    => true,
				'returnUrl' => 'index.php?option=com_foftest&view=foobars'
			)
		);

		$data[] = array(
			array(
				'publish'   => false,
				'returnurl' => ''
			),
			array(
				'return'    => false,
				'returnUrl' => 'index.php?option=com_foftest&view=foobars'
			)
		);

		$data[] = array(
			array(
				'publish'   => true,
				'returnurl' => base64_encode('index.php?option=com_foftest&view=returnurl')
			),
			array(
				'return'    => true,
				'returnUrl' => 'index.php?option=com_foftest&view=returnurl'
			)
		);

		return $data;
	}

	public static function getTestSetAccess()
	{
		$item = FOFTable::getAnInstance('Foobar', 'FoftestTable');
		$data[] = array(
			array(
				'save'      => true,
				'returnurl' => '',
				'id'        => 2,
				'item'      => $item,
				'loadid'    => 2,
				'level'     => 3
			),
			array(
				'return'    => true,
				'returnUrl' => 'index.php?option=com_foftest&view=foobars',
				'level'     => 3
			)
		);

		$data[] = array(
			array(
				'save'      => false,
				'returnurl' => '',
				'id'        => 2,
				'item'      => $item,
				'loadid'    => 2,
				'level'     => 3
			),
			array(
				'return'    => false,
				'returnUrl' => 'index.php?option=com_foftest&view=foobars',
				'level'     => 3
			)
		);

		$data[] = array(
			array(
				'save'      => false,
				'returnurl' => '',
				'id'        => 2,
				'item'      => $item,
				'loadid'    => 3,
				'level'     => 3
			),
			array(
				'return'    => false,
				'returnUrl' => 'index.php?option=com_foftest&view=foobars',
				'level'     => 1
			)
		);

		$data[] = array(
			array(
				'save'      => true,
				'returnurl' => base64_encode('index.php?option=com_foftest&view=returnurl'),
				'id'        => 2,
				'item'      => $item,
				'loadid'    => 2,
				'level'     => 3
			),
			array(
				'return'    => true,
				'returnUrl' => 'index.php?option=com_foftest&view=returnurl',
				'level'     => 3
			)
		);

		$data[] = array(
			array(
				'save'      => false,
				'returnurl' => base64_encode('index.php?option=com_foftest&view=returnurl'),
				'id'        => 2,
				'item'      => $item,
				'loadid'    => 2,
				'level'     => 3
			),
			array(
				'return'    => false,
				'returnUrl' => 'index.php?option=com_foftest&view=returnurl',
				'level'     => 3
			)
		);

		return $data;
	}

	public static function getTestGetModel()
	{
        // Everything is automatically set
		$data[] = array(
            array(
                'name'   => '',
                'prefix' => '',
                'config' => '',
                'model'  => true
            ),
            array(
                'name'   => 'foftest',
                'prefix' => 'foftestModel',
                'config' => '',
                'return' => true
            )
		);

        $data[] = array(
            array(
                'name'   => 'Foobar',
                'prefix' => '',
                'config' => '',
                'model'  => true
            ),
            array(
                'name'   => 'Foobar',
                'prefix' => 'foftestModel',
                'config' => '',
                'return' => true
            )
        );

        $data[] = array(
            array(
                'name'   => 'Foobar',
                'prefix' => 'DummyModel',
                'config' => '',
                'model'  => true
            ),
            array(
                'name'   => 'Foobar',
                'prefix' => 'DummyModel',
                'config' => '',
                'return' => true
            )
        );

        $data[] = array(
            array(
                'name'   => 'Foobar',
                'prefix' => 'DummyModel',
                'config' => array('input' => new FOFInput(array('test' => 'test'))),
                'model'  => true
            ),
            array(
                'name'   => 'Foobar',
                'prefix' => 'DummyModel',
                'config' => array('input' => new FOFInput(array('test' => 'test'))),
                'return' => true
            )
        );

        $data[] = array(
            array(
                'name'   => '',
                'prefix' => '',
                'config' => '',
                'model'  => false
            ),
            array(
                'name'   => 'foftest',
                'prefix' => 'foftestModel',
                'config' => '',
                'return' => false
            )
        );

		return $data;
	}

    public static function getTestGetName()
    {
        $data[] = array(
            array(
                'name'      => '',
                'component' => ''
            ),
            array(
                'name' => 'fof30\\controller\\'
            )
        );

        $data[] = array(
            array(
                'name'      => 'dummy',
                'component' => ''
            ),
            array(
                'name' => 'dummy'
            )
        );

        $data[] = array(
            array(
                'name'      => '',
                'component' => 'dummy'
            ),
            array(
                'name' => 'dummy'
            )
        );

        return $data;
    }

    public static function getTestGetView()
    {
        $data[] = array(
            array(
                'cache'  => false,
                'name'   => '',
                'type'   => '',
                'prefix' => '',
                'config' => ''
            ),
            array(
                'name'   => 'foftest',
                'type'   => '',
                'prefix' => 'foftestView',
                'config' => array(),
                'return' => true
            )
        );

        $data[] = array(
            array(
                'cache'  => false,
                'name'   => 'dummy',
                'type'   => '',
                'prefix' => '',
                'config' => ''
            ),
            array(
                'name'   => 'dummy',
                'type'   => '',
                'prefix' => 'foftestView',
                'config' => array(),
                'return' => true
            )
        );

        $data[] = array(
            array(
                'cache'  => false,
                'name'   => 'dummy',
                'type'   => 'html',
                'prefix' => '',
                'config' => ''
            ),
            array(
                'name'   => 'dummy',
                'type'   => 'html',
                'prefix' => 'foftestView',
                'config' => array(),
                'return' => true
            )
        );

        $data[] = array(
            array(
                'cache'  => false,
                'name'   => 'dummy',
                'type'   => 'html',
                'prefix' => 'foobarView',
                'config' => ''
            ),
            array(
                'name'   => 'dummy',
                'type'   => 'html',
                'prefix' => 'foobarView',
                'config' => array(),
                'return' => true
            )
        );

        $data[] = array(
            array(
                'cache'  => false,
                'name'   => 'dummy',
                'type'   => 'html',
                'prefix' => 'foobarView',
                'config' => array('test' => 123)
            ),
            array(
                'name'   => 'dummy',
                'type'   => 'html',
                'prefix' => 'foobarView',
                'config' => array('test' => 123),
                'return' => true
            )
        );

        $data[] = array(
            array(
                'cache'  => false,
                'name'   => 'dummy',
                'type'   => 'html',
                'prefix' => 'foobarView',
                'config' => (object)array('test' => 123)
            ),
            array(
                'name'   => 'dummy',
                'type'   => 'html',
                'prefix' => 'foobarView',
                'config' => array('test' => 123),
                'return' => true
            )
        );

        $data[] = array(
            array(
                'cache'  => true,
                'name'   => '',
                'type'   => '',
                'prefix' => '',
                'config' => ''
            ),
            array(
                'name'   => 'foftest',
                'type'   => '',
                'prefix' => 'foftestView',
                'config' => array(),
                'return' => 'cache'
            )
        );

        return $data;
    }
}
