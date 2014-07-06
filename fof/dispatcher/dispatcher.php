<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  dispatcher
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace FOF30\Dispatcher;

use FOF30\Config\Provider;
use FOF30\Platform\Platform;
use FOF30\Utils\Filefinder\Filefinder;
use FOF30\Utils\Mvc\Base;
use FOF30\Controller\Controller as FOFController;
use FOF30\Encrypt\Aes as FOFEncryptAes;
use FOF30\Encrypt\Totp as FOFEncryptTotp;
use FOF30\Inflector\Inflector as FOFInflector;
use FOF30\Input\Input;
use FOF30\Platform\Platform as FOFPlatform;

// Joomla! class inclusion
use JText, JLoader, Exception;

// Protect from unauthorized access
defined('FOF30_INCLUDED') or die;

/**
 * FrameworkOnFramework dispatcher class
 *
 * FrameworkOnFramework is a set of classes which extend Joomla! 1.5 and later's
 * MVC framework with features making maintaining complex software much easier,
 * without tedious repetitive copying of the same code over and over again.
 *
 * @package  FrameworkOnFramework
 * @since    1.0
 */
class Dispatcher extends Base
{

	/** @var string The name of the default view, in case none is specified */
	public $defaultView = 'cpanel';

	// Variables for FOF's transparent user authentication. You can override them
	// in your Dispatcher's __construct() method.

	/** @var int The Time Step for the TOTP used in FOF's transparent user authentication */
	protected $fofAuth_timeStep = 6;

	/** @var string The key for the TOTP, Base32 encoded (watch out; Base32, NOT Base64!) */
	protected $fofAuth_Key = null;

	/** @var array Which formats to be handled by transparent authentication */
	protected $fofAuth_Formats = array('json', 'csv', 'xml', 'raw');

	/**
	 * Should I logout the transparently authenticated user on logout?
	 * Recommended to leave it on in order to avoid crashing the sessions table.
	 *
	 * @var boolean
	 */
	protected $fofAuth_LogoutOnReturn = true;

	/** @var array Which methods to use to fetch authentication credentials and in which order */
	protected $fofAuth_AuthMethods = array(
		/* HTTP Basic Authentication using encrypted information protected
		 * with a TOTP (the username must be "_fof_auth") */
		'HTTPBasicAuth_TOTP',
		/* Encrypted information protected with a TOTP passed in the
		 * _fofauthentication query string parameter */
		'QueryString_TOTP',
		/* HTTP Basic Authentication using a username and password pair in plain text */
		'HTTPBasicAuth_Plaintext',
		/* Plaintext, JSON-encoded username and password pair passed in the
		 * _fofauthentication query string parameter */
		'QueryString_Plaintext',
		/* Plaintext username and password in the _fofauthentication_username
		 * and _fofauthentication_username query string parameters */
		'SplitQueryString_Plaintext',
	);

	/** @var bool Did we successfully and transparently logged in a user? */
	private $_fofAuth_isLoggedIn = false;

	/** @var string The calculated encryption key for the _TOTP methods, used if we have to encrypt the reply */
	private $_fofAuth_CryptoKey = '';

	/**
	 * Get a static (Singleton) instance of a particular Dispatcher
	 *
	 * @param   string $option The component name
	 * @param   string $view   The View name
	 * @param   array  $config Configuration data
	 *
	 * @staticvar  array  $instances  Holds the array of Dispatchers FOF knows about
	 *
	 * @return  Dispatcher
	 */
	public static function &getAnInstance($option = null, $view = null, $config = array())
	{
		static $instances = array();

		$hash = $option . $view;

		if (!array_key_exists($hash, $instances))
		{
			$instances[$hash] = self::getTmpInstance($option, $view, $config);
		}

		return $instances[$hash];
	}

	/**
	 * Gets a temporary instance of a Dispatcher
	 *
	 * @param   string $option The component name
	 * @param   string $view   The View name
	 * @param   array  $config Configuration data
	 *
	 * @return Dispatcher
	 */
	public static function &getTmpInstance($option = null, $view = null, $config = array())
	{
		if (is_null($config))
		{
			$config = array();
		}

		if (!is_array($config))
		{
			$config = array();
		}

		// Get the input for this MVC triad
		$input = isset($config['input']) ? $config['input'] : null;
		$input_options = isset($config['input_options']) ? $config['input_options'] : array();
		$input_options = !is_array($input_options) ? array() : $input_options;
		$input = ($input instanceof Input) ? $input : new Input($input, $input_options);
		$config['input'] = $input;

		$config['option'] = !is_null($option) ? $option : $input->getCmd('option', 'com_foobar');
		$config['view'] = !is_null($view) ? $view : $input->getCmd('view', '');

		$input->set('option', $config['option']);
		$input->set('view', $config['view']);

		// Load the configuration provider
		$configProvider = new Provider();

		// Get the vendor name
		$config['vendor'] = isset($config['vendor']) ? $config['vendor'] : 'Component';
		$config['vendor'] = $configProvider->get($config['option']. '.config.vendor', $config['vendor']);

		// Get the application side
		$appSide = Platform::getInstance()->isBackend() ? 'Backend' : 'Frontend';
		$config['application_side'] = isset($config['application_side']) ? $config['application_side'] : $appSide;

		$classParts = Filefinder::getClassFile($config['vendor'], $config['option'], 'Dispatcher', null, null);

		$className = $classParts['class'];

		$instance = new $className($config);

		return $instance;
	}

	/**
	 * Public constructor
	 *
	 * @param   array $config The configuration variables
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->defaultView = $this->configProvider->get($this->component . '.dispatcher.default_view', $this->defaultView);

		// Get the default values for the view name
		$this->view = $this->input->getCmd('view', null);

		if (empty($this->view))
		{
			// Do we have a task formatted as controller.task?
			$task = $this->config['task'];

			if (!empty($task) && (strstr($task, '.') !== false))
			{
				list($this->view, $this->task) = explode('.', $task, 2);
				$this->config['task'] = $this->task;
			}
		}

		if (empty($this->view))
		{
			$this->view = $this->defaultView;
		}

		if (array_key_exists('view', $config))
		{
			$this->view = empty($config['view']) ? $this->view : $config['view'];
		}

		$this->config['option'] = $this->component;
		$this->config['view'] = $this->view;
		$this->config['layout'] = $this->layout;
	}

	/**
	 * The main code of the Dispatcher. It spawns the necessary controller and
	 * runs it.
	 *
	 * @throws Exception
	 *
	 * @return  void|Exception
	 */
	public function dispatch()
	{
		$platform = FOFPlatform::getInstance();

		// ACL check for the back-end
		if (!$platform->authorizeAdmin($this->component))
		{
			return $platform->raiseError(403, JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
		}

		$this->transparentAuthentication();

		// Merge English and local translations
		$platform->loadTranslations($this->component);

		$canDispatch = true;

		if ($platform->isCli())
		{
			$canDispatch = $canDispatch && $this->onBeforeDispatchCLI();
		}

		$canDispatch = $canDispatch && $this->onBeforeDispatch();

		if (!$canDispatch)
		{
			// We can set header only if we're not in CLI
			if (!$platform->isCli())
			{
				$platform->setHeader('Status', '403 Forbidden', true);
			}

			return $platform->raiseError(403, JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
		}

		// Get and execute the controller
		if (empty($this->task))
		{
			$this->task = $this->getTask($this->view);
		}

		// Pluralise/sungularise the view name for typical tasks
		if (in_array($this->task, array('edit', 'add', 'read')))
		{
			$this->view = FOFInflector::singularize($this->view);
		}
		elseif (in_array($this->task, array('browse')))
		{
			$this->view = FOFInflector::pluralize($this->view);
		}

		$this->config['view'] = $this->view;
		$this->config['task'] = $this->task;

		$controller = FOFController::getTmpInstance($this->component, $this->view, $this->config);
		$status = $controller->execute($this->task);

		if (!$this->onAfterDispatch())
		{
			// We can set header only if we're not in CLI
			if (!$platform->isCli())
			{
				$platform->setHeader('Status', '403 Forbidden', true);
			}

			return $platform->raiseError(403, JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
		}

		$format = $this->input->get('format', 'html', 'cmd');
		$format = empty($format) ? 'html' : $format;

		if ($controller->hasRedirect())
		{
			$controller->redirect();
		}
	}

	/**
	 * Tries to guess the controller task to execute based on the view name and
	 * the HTTP request method.
	 *
	 * @param   string $view The name of the view
	 *
	 * @return  string  The best guess of the task to execute
	 */
	protected function getTask($view)
	{
		// Get a default task based on plural/singular view
		$request_task = $this->input->getCmd('task', null);
		$task = FOFInflector::isPlural($view) ? 'browse' : 'edit';

		// Get a potential ID, we might need it later
		$id = $this->input->get('id', null, 'int');

		if ($id == 0)
		{
			$ids = $this->input->get('ids', array(), 'array');

			if (!empty($ids))
			{
				$id = array_shift($ids);
			}
		}

		// Check the request method

		if (!isset($_SERVER['REQUEST_METHOD']))
		{
			$_SERVER['REQUEST_METHOD'] = 'GET';
		}

		$requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);

		switch ($requestMethod)
		{
			case 'POST':
			case 'PUT':
				if (!is_null($id))
				{
					$task = 'save';
				}
				break;

			case 'DELETE':
				if ($id != 0)
				{
					$task = 'delete';
				}
				break;

			case 'GET':
			default:
				// If it's an edit without an ID or ID=0, it's really an add
				if (($task == 'edit') && ($id == 0))
				{
					$task = 'add';
				}

				// If it's an edit in the frontend, it's really a read
				elseif (($task == 'edit') && FOFPlatform::getInstance()->isFrontend())
				{
					$task = 'read';
				}
				break;
		}

		return $task;
	}

	/**
	 * Executes right before the dispatcher tries to instantiate and run the
	 * controller.
	 *
	 * @return  boolean  Return false to abort
	 */
	public function onBeforeDispatch()
	{
		return true;
	}

	/**
	 * Sets up some environment variables, so we can work as usually on CLI, too.
	 *
	 * @return  boolean  Return false to abort
	 */
	public function onBeforeDispatchCLI()
	{
		JLoader::import('joomla.environment.uri');
		JLoader::import('joomla.application.component.helper');

		// Trick to create a valid url used by JURI
		$this->_originalPhpScript = '';

		// We have no Application Helper (there is no Application!), so I have to define these constants manually
		$option = $this->input->get('option', '', 'cmd');

		if ($option)
		{
			$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($option);

			if (!defined('JPATH_COMPONENT'))
			{
				define('JPATH_COMPONENT', $componentPaths['main']);
			}

			if (!defined('JPATH_COMPONENT_SITE'))
			{
				define('JPATH_COMPONENT_SITE', $componentPaths['site']);
			}

			if (!defined('JPATH_COMPONENT_ADMINISTRATOR'))
			{
				define('JPATH_COMPONENT_ADMINISTRATOR', $componentPaths['admin']);
			}
		}

		return true;
	}

	/**
	 * Executes right after the dispatcher runs the controller.
	 *
	 * @return  boolean  Return false to abort
	 */
	public function onAfterDispatch()
	{
		// If we have to log out the user, please do so now
		if ($this->fofAuth_LogoutOnReturn && $this->_fofAuth_isLoggedIn)
		{
			FOFPlatform::getInstance()->logoutUser();
		}

		return true;
	}

	/**
	 * Transparently authenticates a user
	 *
	 * @return  void
	 */
	public function transparentAuthentication()
	{
		// Only run when there is no logged in user
		if (!FOFPlatform::getInstance()->getUser()->guest)
		{
			return;
		}

		// Check the format
		$format = $this->input->getCmd('format', 'html');

		if (!in_array($format, $this->fofAuth_Formats))
		{
			return;
		}

		foreach ($this->fofAuth_AuthMethods as $method)
		{
			// If we're already logged in, don't bother
			if ($this->_fofAuth_isLoggedIn)
			{
				continue;
			}

			// This will hold our authentication data array (username, password)
			$authInfo = null;

			switch ($method)
			{
				case 'HTTPBasicAuth_TOTP':

					if (empty($this->fofAuth_Key))
					{
						continue;
					}

					if (!isset($_SERVER['PHP_AUTH_USER']))
					{
						continue;
					}

					if (!isset($_SERVER['PHP_AUTH_PW']))
					{
						continue;
					}

					if ($_SERVER['PHP_AUTH_USER'] != '_fof_auth')
					{
						continue;
					}

					$encryptedData = $_SERVER['PHP_AUTH_PW'];

					$authInfo = $this->_decryptWithTOTP($encryptedData);
					break;

				case 'QueryString_TOTP':
					$encryptedData = $this->input->get('_fofauthentication', '', 'raw');

					if (empty($encryptedData))
					{
						continue;
					}

					$authInfo = $this->_decryptWithTOTP($encryptedData);
					break;

				case 'HTTPBasicAuth_Plaintext':
					if (!isset($_SERVER['PHP_AUTH_USER']))
					{
						continue;
					}

					if (!isset($_SERVER['PHP_AUTH_PW']))
					{
						continue;
					}

					$authInfo = array(
						'username' => $_SERVER['PHP_AUTH_USER'],
						'password' => $_SERVER['PHP_AUTH_PW']
					);
					break;

				case 'QueryString_Plaintext':
					$jsonencoded = $this->input->get('_fofauthentication', '', 'raw');

					if (empty($jsonencoded))
					{
						continue;
					}

					$authInfo = json_decode($jsonencoded, true);

					if (!is_array($authInfo))
					{
						$authInfo = null;
					}
					elseif (!array_key_exists('username', $authInfo) || !array_key_exists('password', $authInfo))
					{
						$authInfo = null;
					}
					break;

				case 'SplitQueryString_Plaintext':
					$authInfo = array(
						'username' => $this->input->get('_fofauthentication_username', '', 'raw'),
						'password' => $this->input->get('_fofauthentication_password', '', 'raw'),
					);

					if (empty($authInfo['username']))
					{
						$authInfo = null;
					}

					break;

				default:
					continue;

					break;
			}

			// No point trying unless we have a username and password
			if (!is_array($authInfo))
			{
				continue;
			}

			$this->_fofAuth_isLoggedIn = FOFPlatform::getInstance()->loginUser($authInfo);
		}
	}

	/**
	 * Decrypts a transparent authentication message using a TOTP
	 *
	 * @param   string $encryptedData The encrypted data
	 *
	 * @codeCoverageIgnore
	 * @return  array  The decrypted data
	 */
	private function _decryptWithTOTP($encryptedData)
	{
		if (empty($this->fofAuth_Key))
		{
			$this->_fofAuth_CryptoKey = null;

			return null;
		}

		$totp = new FOFEncryptTotp($this->fofAuth_timeStep);
		$period = $totp->getPeriod();
		$period--;

		for ($i = 0; $i <= 2; $i++)
		{
			$time = ($period + $i) * $this->fofAuth_timeStep;
			$otp = $totp->getCode($this->fofAuth_Key, $time);
			$this->_fofAuth_CryptoKey = hash('sha256', $this->fofAuth_Key . $otp);

			$aes = new FOFEncryptAes($this->_fofAuth_CryptoKey);
			$ret = $aes->decryptString($encryptedData);
			$ret = rtrim($ret, "\000");

			$ret = json_decode($ret, true);

			if (!is_array($ret))
			{
				continue;
			}

			if (!array_key_exists('username', $ret))
			{
				continue;
			}

			if (!array_key_exists('password', $ret))
			{
				continue;
			}

			// Successful decryption!
			return $ret;
		}

		// Obviously if we're here we could not decrypt anything. Bail out.
		$this->_fofAuth_CryptoKey = null;

		return null;
	}

	/**
	 * Main function to detect if we're running in a CLI environment and we're admin
	 *
	 * @return  array  isCLI and isAdmin. It's not an associtive array, so we can use list.
	 */
	public static function isCliAdmin()
	{
		static $isCLI = null;
		static $isAdmin = null;

		if (is_null($isCLI) && is_null($isAdmin))
		{
			$isCLI = FOFPlatform::getInstance()->isCli();
			$isAdmin = FOFPlatform::getInstance()->isBackend();
		}

		return array($isCLI, $isAdmin);
	}
}
