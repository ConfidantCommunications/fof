<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  platform
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace FOF30\Platform;

use FOF30\Inflector\Inflector;
use FOF30\Input\Input as FOFInput;

use JFactory, JLoader, JUri, JRegistry, JError, JException, JApplicationCli, JUser, JDocument, JPluginHelper, JLanguage;
use JEventDispatcher, JDispatcher, JAuthentication, JUserHelper, JLog, JResponse, JVersion, JDate, JDatabaseDriver;

// Protect from unauthorized access
defined('FOF30_INCLUDED') or die;

/**
 * Part of the FOF Platform Abstraction Layer. It implements everything that
 * depends on the platform FOF is running under, e.g. the Joomla! CMS front-end,
 * the Joomla! CMS back-end, a CLI Joomla! Platform app, a bespoke Joomla!
 * Platform / Framework web application and so on.
 *
 * This is the abstract class implementing some basic housekeeping functionality
 * and provides the static interface to get the appropriate Platform object for
 * use in the rest of the framework.
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class Platform
{
	/**
	 * The table and table field cache object, used to speed up database access
	 *
	 * @var  JRegistry|null
	 */
	protected $_cache = null;

	/**
	 * Cached filesystem abstraction object
	 *
	 * @var \FOF30\Integration\Joomla\Filesystem\Filesystem
	 *
	 * @since 3.0.0
	 */
	protected $filesystemObject = null;

	/**
	 * The list of paths where platform class files will be looked for
	 *
	 * @var  array
	 */
	protected static $paths = array();

	/**
	 * The platform class instance which will be returned by getInstance
	 *
	 * @var  Platform
	 */
	protected static $instance = null;

	// ========================================================================
	// Public API for platform integration handling
	// ========================================================================

	/**
	 * Force a specific platform object to be used. If null, nukes the cache
	 *
	 * @param   Platform|null $instance The Platform object to be used
	 *
	 * @return  void
	 */
	public static function forceInstance($instance)
	{
		if ($instance instanceof Platform || is_null($instance))
		{
			self::$instance = $instance;
		}
	}

	/**
	 * Find and return the most relevant platform object
	 *
	 * @return  Platform
	 */
	public static function getInstance()
	{
		if (!is_object(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Returns the filesystem integration object
	 *
	 * @return \FOF30\Integration\Joomla\Filesystem\Filesystem
	 *
	 * @since 3.0.0
	 */
	public function getFilesystemObject()
	{
		if (empty($this->filesystemObject))
		{
			$this->filesystemObject = new \FOF30\Integration\Joomla\Filesystem\Filesystem();
		}

		return $this->filesystemObject;
	}

	/**
	 * Forces a filesystem integration object instance
	 *
	 * @param   object $object The object to force for this key
	 *
	 * @return  void
	 *
	 * @since  3.0.0
	 */
	public function setFilesystemObject($object)
	{
		$this->filesystemObject = $object;
	}

	/**
	 * Returns a platform integration object
	 *
	 * @param   string $key The key name of the platform integration object, e.g. 'filesystem'
	 *
	 * @return  object
	 *
	 * @since      2.1.2
	 *
	 * @deprecated since 3.0.0, use getFilesystemObject instead
	 *
	 * @throws \Exception When the key is not 'filesystem'
	 */
	public function getIntegrationObject($key)
	{
		if ($key != 'filesystem')
		{
			throw new \Exception("\\FOF30\\Platform\\Platform::getIntegrationObject can only return a filesystem object but $key object type requested.", 500);
		}

		$this->logDeprecated('\FOF30\Platform\Platform::getIntegrationObject is deprecated. Use Platform::getFilesystemObject instead');

		return $this->getFilesystemObject();
	}

	/**
	 * Forces a platform integration object instance
	 *
	 * @param   string $key    The key name of the platform integration object, e.g. 'filesystem'
	 * @param   object $object The object to force for this key
	 *
	 * @return  object
	 *
	 * @since      2.1.2
	 *
	 * @deprecated since 3.0.0, use setFilesystemObject instead
	 *
	 * @throws \Exception When the key is not 'filesystem'
	 */
	public function setIntegrationObject($key, $object)
	{
		if ($key != 'filesystem')
		{
			throw new \Exception("\\FOF30\\Platform\\Platform::getIntegrationObject can only return a filesystem object but $key object type requested.", 500);
		}

		$this->logDeprecated('\FOF30\Platform\Platform::getIntegrationObject is deprecated. Use Platform::getFilesystemObject instead');

		$this->setFilesystemObject($object);
	}

	// ========================================================================
	// Platform Implementation
	// ========================================================================

	/**
	 * Checks if the current script is run inside a valid CMS execution
	 *
	 * @return bool
	 */
	public function checkExecution()
	{
		return defined('_JEXEC');
	}

	/**
	 * Throw an error in a platform-friendly manner
	 *
	 * @param int    $code    The error code
	 * @param string $message The error message
	 *
	 * @return JError on Joomla! 2.5 (exception thrown on Joomla! 3+)
	 *
	 * @throws \Exception
	 */
	public function raiseError($code, $message)
	{
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			throw new \Exception($message, $code);
		}
		else
		{
			return JError::raiseError($code, $message);
		}
	}

	/**
	 * Set the error Handling, if possible
	 *
	 * @param   integer $level     PHP error level (E_ALL)
	 * @param   string  $log_level What to do with the error (ignore, callback)
	 * @param   array   $options   Options for the error handler
	 *
	 * @return  void
	 */
	public function setErrorHandling($level, $log_level, $options = array())
	{
		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			JError::setErrorHandling($level, $log_level, $options);
		}
	}

	/**
	 * Returns absolute path to directories used by the CMS.
	 *
	 * The keys returned are:
	 * root        The root of the installation
	 * public    Location of public site (frontend)
	 * admin    Location of administrator site (backend)
	 * tmp        Temporary directory
	 * log        Logs directory
	 *
	 * @return  array  A hash array with keys root, public, admin, tmp and log.
	 */
	public function getPlatformBaseDirs()
	{
		return array(
			'root'   => JPATH_ROOT,
			'public' => JPATH_SITE,
			'admin'  => JPATH_ADMINISTRATOR,
			'tmp'    => JFactory::getConfig()->get('tmp_dir'),
			'log'    => JFactory::getConfig()->get('log_dir')
		);
	}

	/**
	 * Returns the base (root) directories for a given component. The
	 * "component" is used in the sense of what we call "component" in Joomla!,
	 * "plugin" in WordPress and "module" in Drupal, i.e. an application which
	 * is running inside our main application (CMS).
	 *
	 * The return is a table with the following keys:
	 * * main    The normal location of component files. For a back-end Joomla!
	 *          component this is the administrator/components/com_example
	 *          directory.
	 * * alt    The alternate location of component files. For a back-end
	 *          Joomla! component this is the front-end directory, e.g.
	 *          components/com_example
	 * * site    The location of the component files serving the public part of
	 *          the application.
	 * * admin    The location of the component files serving the administrative
	 *          part of the application.
	 *
	 * All paths MUST be absolute. All four paths MAY be the same if the
	 * platform doesn't make a distinction between public and private parts,
	 * or when the component does not provide both a public and private part.
	 * All of the directories MUST be defined and non-empty.
	 *
	 * @param   string $component   The name of the component. For Joomla! this
	 *                              is something like "com_example"
	 *
	 * @return  array  A hash array with keys main, alt, site and admin.
	 */
	public function getComponentBaseDirs($component)
	{
		if ($this->isFrontend())
		{
			$mainPath = JPATH_SITE . '/components/' . $component;
			$altPath = JPATH_ADMINISTRATOR . '/components/' . $component;
		}
		else
		{
			$mainPath = JPATH_ADMINISTRATOR . '/components/' . $component;
			$altPath = JPATH_SITE . '/components/' . $component;
		}

		return array(
			'main'  => $mainPath,
			'alt'   => $altPath,
			'site'  => JPATH_SITE . '/components/' . $component,
			'admin' => JPATH_ADMINISTRATOR . '/components/' . $component,
		);
	}

	/**
	 * Return a list of the view template paths for this component. The paths
	 * are in the format site:/component_name/view_name/layout_name or
	 * admin:/component_name/view_name/layout_name
	 *
	 * The list of paths returned is a prioritised list. If a file is
	 * found in the first path the other paths will not be scanned.
	 *
	 * @param   string  $component   The name of the component. For Joomla! this
	 *                               is something like "com_example"
	 * @param   string  $view        The name of the view you're looking a
	 *                               template for
	 * @param   string  $layout      The layout name to load, e.g. 'default'
	 * @param   string  $tpl         The sub-template name to load (null by default)
	 * @param   boolean $strict      If true, only the specified layout will be
	 *                               searched for. Otherwise we'll fall back to
	 *                               the 'default' layout if the specified layout
	 *                               is not found.
	 *
	 * @return  array
	 */
	public function getViewTemplatePaths($component, $view, $layout = 'default', $tpl = null, $strict = false)
	{
		$isAdmin = $this->isBackend();

		$basePath = $isAdmin ? 'admin:' : 'site:';
		$basePath .= $component . '/';
		$altBasePath = $basePath;
		$basePath .= $view . '/';
		$altBasePath .= (Inflector::isSingular($view) ? Inflector::pluralize($view) : Inflector::singularize($view)) . '/';

		if ($strict)
		{
			$paths = array(
				$basePath . $layout . ($tpl ? "_$tpl" : ''),
				$altBasePath . $layout . ($tpl ? "_$tpl" : ''),
			);
		}
		else
		{
			$paths = array(
				$basePath . $layout . ($tpl ? "_$tpl" : ''),
				$basePath . $layout,
				$basePath . 'default' . ($tpl ? "_$tpl" : ''),
				$basePath . 'default',
				$altBasePath . $layout . ($tpl ? "_$tpl" : ''),
				$altBasePath . $layout,
				$altBasePath . 'default' . ($tpl ? "_$tpl" : ''),
				$altBasePath . 'default',
			);
			$paths = array_unique($paths);
		}

		return $paths;
	}

	/**
	 * Get application-specific suffixes to use with template paths. This allows
	 * you to look for view template overrides based on the application version.
	 *
	 * @return  array  A plain array of suffixes to try in template names
	 */
	public function getTemplateSuffixes()
	{
		$jversion = new JVersion;
		$versionParts = explode('.', $jversion->RELEASE);
		$majorVersion = array_shift($versionParts);
		$suffixes = array(
			'.j' . str_replace('.', '', $jversion->getHelpVersion()),
			'.j' . $majorVersion,
		);

		return $suffixes;
	}

	/**
	 * Return the absolute path to the application's template overrides
	 * directory for a specific component. We will use it to look for template
	 * files instead of the regular component directorues. If the application
	 * does not have such a thing as template overrides return an empty string.
	 *
	 * @param   string  $component The name of the component for which to fetch the overrides
	 * @param   boolean $absolute  Should I return an absolute or relative path?
	 *
	 * @return  string  The path to the template overrides directory
	 */
	public function getTemplateOverridePath($component, $absolute = true)
	{
		list($isCli, $isAdmin) = $this->isCliAdmin();

		if (!$isCli)
		{
			if ($absolute)
			{
				$path = JPATH_THEMES . '/';
			}
			else
			{
				$path = $isAdmin ? 'administrator/templates/' : 'templates/';
			}

			if (substr($component, 0, 7) == 'media:/')
			{
				$directory = 'media/' . substr($component, 7);
			}
			else
			{
				$directory = 'html/' . $component;
			}

			$path .= JFactory::getApplication()->getTemplate() .
				'/' . $directory;
		}
		else
		{
			$path = '';
		}

		return $path;
	}

	/**
	 * Load the translation files for a given component. The
	 * "component" is used in the sense of what we call "component" in Joomla!,
	 * "plugin" in WordPress and "module" in Drupal, i.e. an application which
	 * is running inside our main application (CMS).
	 *
	 * @param   string $component   The name of the component. For Joomla! this
	 *                              is something like "com_example"
	 *
	 * @return  void
	 */
	public function loadTranslations($component)
	{
		if ($this->isBackend())
		{
			$paths = array(JPATH_ROOT, JPATH_ADMINISTRATOR);
		}
		else
		{
			$paths = array(JPATH_ADMINISTRATOR, JPATH_ROOT);
		}

		$jlang = JFactory::getLanguage();
		$jlang->load($component, $paths[0], 'en-GB', true);
		$jlang->load($component, $paths[0], null, true);
		$jlang->load($component, $paths[1], 'en-GB', true);
		$jlang->load($component, $paths[1], null, true);
	}

	/**
	 * By default FOF will only use the Controller's onBefore* methods to
	 * perform user authorisation. In some cases, like the Joomla! back-end,
	 * you alos need to perform component-wide user authorisation in the
	 * Dispatcher. This method MUST implement this authorisation check. If you
	 * do not need this in your platform, please always return true.
	 *
	 * @param   string $component The name of the component.
	 *
	 * @return  boolean  True to allow loading the component, false to halt loading
	 */
	public function authorizeAdmin($component)
	{
		if ($this->isBackend())
		{
			// Master access check for the back-end, Joomla! 1.6 style.
			$user = JFactory::getUser();

			if (!$user->authorise('core.manage', $component)
				&& !$user->authorise('core.admin', $component)
			)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns a user object.
	 *
	 * @param   integer $id   The user ID to load. Skip or use null to retrieve
	 *                        the object for the currently logged in user.
	 *
	 * @return  JUser  The JUser object for the specified user
	 */
	public function getUser($id = null)
	{
		return JFactory::getUser($id);
	}

	/**
	 * Returns the JDocument object which handles this component's response. You
	 * may also return null and FOF will a. try to figure out the output type by
	 * examining the "format" input parameter (or fall back to "html") and b.
	 * FOF will not attempt to load CSS and Javascript files (as it doesn't make
	 * sense if there's no JDocument to handle them).
	 *
	 * @return  JDocument
	 */
	public function getDocument()
	{
		$document = null;

		if (!$this->isCli())
		{
			try
			{
				$document = JFactory::getDocument();
			}
			catch (\Exception $exc)
			{
				$document = null;
			}
		}

		return $document;
	}

	/**
	 * Returns an object to handle dates
	 *
	 * @param   mixed $time     The initial time
	 * @param   null  $tzOffest The timezone offset
	 * @param   bool  $locale   Should I try to load a specific class for current language?
	 *
	 * @return  JDate object
	 */
	public function getDate($time = 'now', $tzOffest = null, $locale = true)
	{
		if ($locale)
		{
			return JFactory::getDate($time, $tzOffest);
		}
		else
		{
			return new JDate($time, $tzOffest);
		}
	}

	/**
	 * Returns Joomla!'s language object
	 *
	 * @return JLanguage
	 */
	public function getLanguage()
	{
		return JFactory::getLanguage();
	}

	/**
	 * Returns Joomla!'s database driver object
	 *
	 * @return JDatabaseDriver
	 */
	public function getDbo()
	{
		return JFactory::getDbo();
	}

	/**
	 * This method will try retrieving a variable from the request (input) data.
	 * If it doesn't exist it will be loaded from the user state, typically
	 * stored in the session. If it doesn't exist there either, the $default
	 * value will be used. If $setUserState is set to true, the retrieved
	 * variable will be stored in the user session.
	 *
	 * @param   string   $key          The user state key for the variable
	 * @param   string   $request      The request variable name for the variable
	 * @param   FOFInput $input        The FOFInput object with the request (input) data
	 * @param   mixed    $default      The default value. Default: null
	 * @param   string   $type         The filter type for the variable data. Default: none (no filtering)
	 * @param   boolean  $setUserState Should I set the user state with the fetched value?
	 *
	 * @return  mixed  The value of the variable
	 */
	public function getUserStateFromRequest($key, $request, $input, $default = null, $type = 'none', $setUserState = true)
	{
		list($isCLI, $isAdmin) = $this->isCliAdmin();

		if ($isCLI)
		{
			return $input->get($request, $default, $type);
		}

		$app = JFactory::getApplication();

		if (method_exists($app, 'getUserState'))
		{
			$old_state = $app->getUserState($key, $default);
		}
		else
		{
			$old_state = null;
		}

		$cur_state = (!is_null($old_state)) ? $old_state : $default;
		$new_state = $input->get($request, null, $type);

		// Save the new value only if it was set in this request
		if ($setUserState)
		{
			if ($new_state !== null)
			{
				$app->setUserState($key, $new_state);
			}
			else
			{
				$new_state = $cur_state;
			}
		}
		elseif (is_null($new_state))
		{
			$new_state = $cur_state;
		}

		return $new_state;
	}

	/**
	 * Load plugins of a specific type. Obviously this seems to only be required
	 * in the Joomla! CMS.
	 *
	 * @param   string   $type       The type of the plugins to be loaded
	 * @param   bool     $loadInCli  Should I also try to load plugins in CLI mode (default: false)
	 *
	 * @return void
	 */
	public function importPlugin($type, $loadInCli = false)
	{
		if ($loadInCli || !$this->isCli())
		{
			JLoader::import('joomla.plugin.helper');
			JPluginHelper::importPlugin($type);
		}
	}

	/**
	 * Execute plugins (system-level triggers) and fetch back an array with
	 * their return values.
	 *
	 * @param   string $event      The event (trigger) name, e.g. onBeforeScratchMyEar
	 * @param   array  $data       A hash array of data sent to the plugins as part of the trigger
	 * @param   bool   $loadInCli  Should I also try to run plugins in CLI mode (default: false)
	 *
	 * @return  array  A simple array containing the resutls of the plugins triggered
	 */
	public function runPlugins($event, $data, $loadInCli = false)
	{
		if ($loadInCli || !$this->isCli())
		{
			// IMPORTANT: DO NOT REPLACE THIS INSTANCE OF JDispatcher WITH ANYTHING ELSE. WE NEED JOOMLA!'S PLUGIN EVENT
			// DISPATCHER HERE, NOT OUR GENERIC EVENTS DISPATCHER
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$dispatcher = JEventDispatcher::getInstance();
			}
			else
			{
				$dispatcher = JDispatcher::getInstance();
			}

			return $dispatcher->trigger($event, $data);
		}
		else
		{
			return array();
		}
	}

	/**
	 * Perform an ACL check. Please note that FOF uses by default the Joomla!
	 * CMS convention for ACL privileges, e.g core.edit for the edit privilege.
	 * If your platform uses different conventions you'll have to override the
	 * FOF defaults using fof.xml or by specialising the controller.
	 *
	 * @param   string $action    The ACL privilege to check, e.g. core.edit
	 * @param   string $assetname The asset name to check, typically the component's name
	 *
	 * @return  boolean  True if the user is allowed this action
	 */
	public function authorise($action, $assetname)
	{
		if ($this->isCli())
		{
			return true;
		}

		return JFactory::getUser()->authorise($action, $assetname);
	}

	/**
	 * Main function to detect if we're running in a CLI environment and we're admin
	 *
	 * @return  array  isCLI and isAdmin. It's not an associative array, so we can use list.
	 */
	protected function isCliAdmin()
	{
		static $isCLI = null;
		static $isAdmin = null;

		if (is_null($isCLI) && is_null($isAdmin))
		{
			try
			{
				if (is_null(JFactory::$application))
				{
					$isCLI = true;
				}
				else
				{
					$app = JFactory::getApplication();
					$isCLI = $app instanceof JException || $app instanceof JApplicationCli;
				}
			}
			catch (\Exception $e)
			{
				$isCLI = true;
			}

			if ($isCLI)
			{
				$isAdmin = false;
			}
			else
			{
				$isAdmin = !JFactory::$application ? false : JFactory::getApplication()->isAdmin();
			}
		}

		return array($isCLI, $isAdmin);
	}

	/**
	 * Is this the administrative section of the component?
	 *
	 * @return  boolean
	 */
	public function isBackend()
	{
		list ($isCli, $isAdmin) = $this->isCliAdmin();

		return $isAdmin && !$isCli;
	}

	/**
	 * Is this the public section of the component?
	 *
	 * @return  boolean
	 */
	public function isFrontend()
	{
		list ($isCli, $isAdmin) = $this->isCliAdmin();

		return !$isAdmin && !$isCli;
	}

	/**
	 * Is this a component running in a CLI application?
	 *
	 * @return  boolean
	 */
	public function isCli()
	{
		list ($isCli, $isAdmin) = $this->isCliAdmin();

		return !$isAdmin && $isCli;
	}

	/**
	 * Is AJAX re-ordering supported? This is 100% Joomla!-CMS specific. All
	 * other platforms should return false and never ask why.
	 *
	 * @return  boolean
	 */
	public function supportsAjaxOrdering()
	{
		return version_compare(JVERSION, '3.0', 'ge');
	}

	/**
	 * Is the global FOF cache enabled?
	 *
	 * @return  boolean
	 */
	public function isGlobalFOFCacheEnabled()
	{
		return !(defined('JDEBUG') && JDEBUG);
	}

	/**
	 * Performs a check between two versions. Use this function instead of PHP version_compare
	 * so we can mock it while testing
	 *
	 * @param   string $version1 First version number
	 * @param   string $version2 Second version number
	 * @param   string $operator Operator (see version_compare for valid operators)
	 *
	 * @deprecated Use PHP's version_compare against JVERSION in your code. This method is scheduled for removal in FOF 3.0
	 *
	 * @return  boolean
	 */
	public function checkVersion($version1, $version2, $operator)
	{
		return version_compare($version1, $version2, $operator);
	}

	/**
	 * Saves something to the cache. This is supposed to be used for system-wide
	 * FOF data, not application data.
	 *
	 * @param   string $key     The key of the data to save
	 * @param   string $content The actual data to save
	 *
	 * @return  boolean  True on success
	 */
	public function setCache($key, $content)
	{
		$registry = $this->getCacheObject();

		$registry->set($key, $content);

		return $this->saveCache();
	}

	/**
	 * Retrieves data from the cache. This is supposed to be used for system-side
	 * FOF data, not application data.
	 *
	 * @param   string $key     The key of the data to retrieve
	 * @param   string $default The default value to return if the key is not found or the cache is not populated
	 *
	 * @return  string  The cached value
	 */
	public function getCache($key, $default = null)
	{
		$registry = $this->getCacheObject();

		return $registry->get($key, $default);
	}

	/**
	 * Gets a reference to the cache object, loading it from the disk if
	 * needed.
	 *
	 * @param   boolean  $force  Should I forcibly reload the registry?
	 *
	 * @return  JRegistry
	 */
	protected function &getCacheObject($force = false)
	{
		// Check if we have to load the cache file or we are forced to do that
		if (is_null($this->_cache) || $force)
		{
			// Create a new JRegistry object
			JLoader::import('joomla.registry.registry');
			$this->_cache = new JRegistry;

			// Try to get data from Joomla!'s cache
			$cache = JFactory::getCache('fof', '');
			$data = $cache->get('cache', 'fof');

			// If data is not found, fall back to the legacy (FOF 2.1.rc3 and earlier) method
			if ($data === false)
			{
				// Find the path to the file
				$cachePath  = JPATH_CACHE . '/fof';
				$filename   = $cachePath . '/cache.php';
				$filesystem = $this->getFilesystemObject();

				// Load the cache file if it exists. JRegistryFormatPHP fails
				// miserably, so I have to work around it.
				if ($filesystem->fileExists($filename))
				{
					@include_once $filename;

					$filesystem->fileDelete($filename);

					$className = 'FOFCacheStorage';

					if (class_exists($className))
					{
						$object = new $className;
						$this->_cache->loadObject($object);

						$cache->store($this->_cache, 'cache', 'fof');
					}
				}
			}
			else
			{
				$this->_cache = $data;
			}
		}

		return $this->_cache;
	}

	/**
	 * Save the cache object back to disk
	 *
	 * @return  boolean  True on success
	 */
	private function saveCache()
	{
		// Get the JRegistry object of our cached data
		$registry = $this->getCacheObject();

		$cache = JFactory::getCache('fof', '');
		return $cache->store($registry, 'cache', 'fof');
	}

	/**
	 * Clears the cache of system-wide FOF data. You are supposed to call this in
	 * your components' installation script post-installation and post-upgrade
	 * methods or whenever you are modifying the structure of database tables
	 * accessed by FOF. Please note that FOF's cache never expires and is not
	 * purged by Joomla!. You MUST use this method to manually purge the cache.
	 *
	 * @return  boolean  True on success
	 */
	public function clearCache()
	{
		$false = false;
		$cache = JFactory::getCache('fof', '');
		$cache->store($false, 'cache', 'fof');
	}

	/**
	 * Returns the Joomla! global configuration object
	 *
	 * @return JRegistry
	 */
	public function getConfig()
	{
		return JFactory::getConfig();
	}

	/**
	 * Logs in a user
	 *
	 * @param   array $authInfo authentification information
	 *
	 * @return  boolean  True on success
	 */
	public function loginUser($authInfo)
	{
		JLoader::import('joomla.user.authentication');
		$options = array('remember'		 => false);
		$authenticate = JAuthentication::getInstance();
		$response = $authenticate->authenticate($authInfo, $options);

		// User failed to authenticate: maybe he enabled two factor authentication?
		// Let's try again "manually", skipping the check vs two factor auth
		// Due the big mess with encryption algorithms and libraries, we are doing this extra check only
		// if we're in Joomla 2.5.18+ or 3.2.1+
		if($response->status != JAuthentication::STATUS_SUCCESS && method_exists('JUserHelper', 'verifyPassword'))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('id, password')
				->from('#__users')
				->where('username=' . $db->quote($authInfo['username']));
			$result = $db->setQuery($query)->loadObject();

			if ($result)
			{

				$match = JUserHelper::verifyPassword($authInfo['password'], $result->password, $result->id);

				if ($match === true)
				{
					// Bring this in line with the rest of the system
					$user = JUser::getInstance($result->id);
					$response->email = $user->email;
					$response->fullname = $user->name;

					if (JFactory::getApplication()->isAdmin())
					{
						$response->language = $user->getParam('admin_language');
					}
					else
					{
						$response->language = $user->getParam('language');
					}

					$response->status = JAuthentication::STATUS_SUCCESS;
					$response->error_message = '';
				}
			}
		}

		if ($response->status == JAuthentication::STATUS_SUCCESS)
		{
			$this->importPlugin('user');
			$results = $this->runPlugins('onLoginUser', array((array) $response, $options));

			JLoader::import('joomla.user.helper');
			$userid = JUserHelper::getUserId($response->username);
			$user = $this->getUser($userid);

			$session = JFactory::getSession();
			$session->set('user', $user);

			return true;
		}

		return false;
	}

	/**
	 * Logs out a user
	 *
	 * @return  boolean  True on success
	 */
	public function logoutUser()
	{
		JLoader::import('joomla.user.authentication');
		$app = JFactory::getApplication();
		$options = array('remember'	 => false);
		$parameters = array('username'	 => $this->getUser()->username);

		return $app->triggerEvent('onLogoutUser', array($parameters, $options));
	}

	/**
	 * Adds a log file for FOF
	 *
	 * @param string $file The name of the file to add
	 */
	public function logAddLogger($file)
	{
		JLog::addLogger(array('text_file' => $file), JLog::ALL, array('fof'));
	}

	/**
	 * Logs a deprecated practice. In Joomla! this results in the $message being output in the
	 * deprecated log file, found in your site's log directory.
	 *
	 * @param   string $message The deprecated practice log message
	 *
	 * @return  void
	 */
	public function logDeprecated($message)
	{
		JLog::add($message, JLog::WARNING, 'deprecated');
	}

	/**
	 * Logs a debug message for FOF
	 *
	 * @param string $message The message to log
	 */
	public function logDebug($message)
	{
		JLog::add($message, JLog::DEBUG, 'fof');
	}

	/**
	 * Returns the root URI for the request.
	 *
	 * @param   boolean  $pathonly  If false, prepend the scheme, host and port information. Default is false.
	 * @param   string   $path      The path
	 *
	 * @return  string  The root URI string.
	 */
	public function URIroot($pathonly = false, $path = null)
	{
		JLoader::import('joomla.environment.uri');

		return JUri::root($pathonly, $path);
	}

	/**
	 * Returns the base URI for the request.
	 *
	 * @param   boolean  $pathonly  If false, prepend the scheme, host and port information. Default is false.
	 * |
	 * @return  string  The base URI string
	 */
	public function URIbase($pathonly = false)
	{
		JLoader::import('joomla.environment.uri');

		return JUri::base($pathonly);
	}

	/**
	 * Method to set a response header.  If the replace flag is set then all headers
	 * with the given name will be replaced by the new one (only if the current platform supports header caching)
	 *
	 * @param   string   $name     The name of the header to set.
	 * @param   string   $value    The value of the header to set.
	 * @param   boolean  $replace  True to replace any headers with the same name.
	 *
	 * @return  void
	 */
	public function setHeader($name, $value, $replace = false)
	{
		if (version_compare(JVERSION, '3.2', 'ge'))
		{
			JFactory::getApplication()->setHeader($name, $value, $replace);
		}
		else
		{
			JResponse::setHeader($name, $value, $replace);
		}
	}

	/**
	 * Outputs all headers immediately
	 *
	 * @throws \Exception
	 */
	public function sendHeaders()
	{
		if (version_compare(JVERSION, '3.2', 'ge'))
		{
			JFactory::getApplication()->sendHeaders();
		}
		else
		{
			JResponse::sendHeaders();
		}
	}
}
