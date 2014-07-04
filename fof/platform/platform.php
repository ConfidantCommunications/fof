<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  platform
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace FOF30\Platform;

use FOF30\Input\Input as FOFInput;

use JDate, JDocument, JUser, JDatabaseDriver, JLanguage, JError;

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
	 * The ordering for this platform class. The lower this number is, the more
	 * important this class becomes. Most important enabled class ends up being
	 * used.
	 *
	 * @var  integer
	 */
	public $ordering = 100;

	/**
	 * The internal name of this platform implementation. It must match the
	 * last part of the platform class name and be in all lowercase letters,
	 * e.g. "foobar" for FOF30\Platform\Foobar
	 *
	 * @var  string
	 *
	 * @since  2.1.2
	 */
	public $name = '';

	/**
	 * The human readable platform name
	 *
	 * @var  string
	 *
	 * @since  2.1.2
	 */
	public $humanReadableName = 'Unknown Platform';

	/**
	 * The platform version string
	 *
	 * @var  string
	 *
	 * @since  2.1.2
	 */
	public $version = '';

	/**
	 * Caches the enabled status of this platform class.
	 *
	 * @var  boolean
	 */
	protected $isEnabled = null;

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
	 * Register a path where platform files will be looked for. These take
	 * precedence over the built-in platform files.
	 *
	 * @param   string  $path  The path to add
	 *
	 * @return  void
	 */
	public static function registerPlatformPath($path)
	{
		if (!in_array($path, self::$paths))
		{
			self::$paths[] = $path;
			self::$instance = null;
		}
	}

	/**
	 * Unregister a path where platform files will be looked for.
	 *
	 * @param   string  $path  The path to remove
	 *
	 * @return  void
	 */
	public static function unregisterPlatformPath($path)
	{
		$pos = array_search($path, self::$paths);

		if ($pos !== false)
		{
			unset(self::$paths[$pos]);
			self::$instance = null;
		}
	}

	/**
	 * Force a specific platform object to be used. If null, nukes the cache
	 *
	 * @param   Platform|null  $instance  The Platform object to be used
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
			// Where to look for platform integrations
			$paths = array(__DIR__ . '/../integration');

			if (is_array(self::$paths))
			{
				$paths = array_merge($paths, self::$paths);
			}

			// Get a list of folders inside this directory
			$integrations = array();

			foreach ($paths as $path)
			{
				if (!is_dir($path))
				{
					continue;
				}

				$di = new \DirectoryIterator($path);
				$temp = array();

				/** @var \DirectoryIterator $fileSpec */
				foreach ($di as $fileSpec)
				{
					if (!$fileSpec->isDir())
					{
						continue;
					}

					$fileName = $fileSpec->getFilename();

					if (substr($fileName, 0, 1) == '.')
					{
						continue;
					}

					$platformFilename = $path . '/' . $fileName . '/platform.php';

					if (!file_exists($platformFilename))
					{
						continue;
					}

					$temp[] = array(
						'classname'		=> '\\FOF30\\Integration\\' . ucfirst($fileName) . '\\Platform',
						'fullpath'		=> $path . '/' . $fileName . '/platform.php',
					);
				}

				$integrations = array_merge($integrations, $temp);
			}

			// Loop all paths
			foreach ($integrations as $integration)
			{
				// Get the class name for this platform class
				$class_name = $integration['classname'];

				// Load the file if the class doesn't exist
				if (!class_exists($class_name, false))
				{
					@include_once $integration['fullpath'];
				}

				// If the class still doesn't exist this file didn't
				// actually contain a platform class; skip it
				if (!class_exists($class_name, false))
				{
					continue;
				}

				// Get an object of this platform
				/** @var Platform $o */
				$o = new $class_name;

				// If it's not enabled, skip it
				if (!$o->isEnabled())
				{
					continue;
				}

				if (is_object(self::$instance))
				{
					// Replace self::$instance if this object has a
					// lower order number
					$current_order = self::$instance->getOrdering();
					$new_order = $o->getOrdering();

					if ($new_order < $current_order)
					{
						self::$instance = null;
						self::$instance = $o;
					}
				}
				else
				{
					// There is no self::$instance already, so use the
					// object we just created.
					self::$instance = $o;
				}
			}
		}

		return self::$instance;
	}

	/**
	 * Returns the ordering of the platform class. Files with a lower ordering
	 * number will be loaded first.
	 *
	 * @return  integer
	 */
	public function getOrdering()
	{
		return $this->ordering;
	}

	/**
	 * Is this platform enabled? This is used for automatic platform detection.
	 * If the environment we're currently running in doesn't seem to be your
	 * platform return false. If many classes return true, the one with the
	 * lowest order will be picked by FOF Platform.
	 *
	 * @return  boolean
	 */
	public function isEnabled()
	{
		if (is_null($this->isEnabled))
		{
			$this->isEnabled = false;
		}

		return $this->isEnabled;
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
	 * @param   object  $object  The object to force for this key
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
	 * @param   string  $key  The key name of the platform integration object, e.g. 'filesystem'
	 *
	 * @return  object
	 *
	 * @since  2.1.2
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
	 * @param   string  $key     The key name of the platform integration object, e.g. 'filesystem'
	 * @param   object  $object  The object to force for this key
	 *
	 * @return  object
	 *
	 * @since  2.1.2
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
	// Default implementation
	// ========================================================================

	/**
	 * Set the error Handling, if possible
	 *
	 * @param   integer  $level      PHP error level (E_ALL)
	 * @param   string   $log_level  What to do with the error (ignore, callback)
	 * @param   array    $options    Options for the error handler
	 *
	 * @return  void
	 */
	public function setErrorHandling($level, $log_level, $options = array())
	{
		if (version_compare(JVERSION, '3.0', 'lt') )
		{
			JError::setErrorHandling($level, $log_level, $options);
		}
	}

	/**
	 * Returns the base (root) directories for a given component. The
	 * "component" is used in the sense of what we call "component" in Joomla!,
	 * "plugin" in WordPress and "module" in Drupal, i.e. an application which
	 * is running inside our main application (CMS).
	 *
	 * The return is a table with the following keys:
	 * * main	The normal location of component files. For a back-end Joomla!
	 *          component this is the administrator/components/com_example
	 *          directory.
	 * * alt	The alternate location of component files. For a back-end
	 *          Joomla! component this is the front-end directory, e.g.
	 *          components/com_example
	 * * site	The location of the component files serving the public part of
	 *          the application.
	 * * admin	The location of the component files serving the administrative
	 *          part of the application.
	 *
	 * All paths MUST be absolute. All four paths MAY be the same if the
	 * platform doesn't make a distinction between public and private parts,
	 * or when the component does not provide both a public and private part.
	 * All of the directories MUST be defined and non-empty.
	 *
	 * @param   string  $component  The name of the component. For Joomla! this
	 *                              is something like "com_example"
	 *
	 * @return  array  A hash array with keys main, alt, site and admin.
	 */
	public function getComponentBaseDirs($component)
	{
		return array(
			'main'	=> '',
			'alt'	=> '',
			'site'	=> '',
			'admin'	=> '',
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
	 * @param   string   $component  The name of the component. For Joomla! this
	 *                               is something like "com_example"
	 * @param   string   $view       The name of the view you're looking a
	 *                               template for
	 * @param   string   $layout     The layout name to load, e.g. 'default'
	 * @param   string   $tpl        The sub-template name to load (null by default)
	 * @param   boolean  $strict     If true, only the specified layout will be
	 *                               searched for. Otherwise we'll fall back to
	 *                               the 'default' layout if the specified layout
	 *                               is not found.
	 *
	 * @return  array
	 */
	public function getViewTemplatePaths($component, $view, $layout = 'default', $tpl = null, $strict = false)
	{
		return array();
	}

	/**
	 * Get application-specific suffixes to use with template paths. This allows
	 * you to look for view template overrides based on the application version.
	 *
	 * @return  array  A plain array of suffixes to try in template names
	 */
	public function getTemplateSuffixes()
	{
		return array();
	}

	/**
	 * Return the absolute path to the application's template overrides
	 * directory for a specific component. We will use it to look for template
	 * files instead of the regular component directorues. If the application
	 * does not have such a thing as template overrides return an empty string.
	 *
	 * @param   string   $component  The name of the component for which to fetch the overrides
	 * @param   boolean  $absolute   Should I return an absolute or relative path?
	 *
	 * @return  string  The path to the template overrides directory
	 */
	public function getTemplateOverridePath($component, $absolute = true)
	{
		return '';
	}

	/**
	 * Load the translation files for a given component. The
	 * "component" is used in the sense of what we call "component" in Joomla!,
	 * "plugin" in WordPress and "module" in Drupal, i.e. an application which
	 * is running inside our main application (CMS).
	 *
	 * @param   string  $component  The name of the component. For Joomla! this
	 *                              is something like "com_example"
	 *
	 * @return  void
	 */
	public function loadTranslations($component)
	{
		return null;
	}

	/**
	 * By default FOF will only use the Controller's onBefore* methods to
	 * perform user authorisation. In some cases, like the Joomla! back-end,
	 * you alos need to perform component-wide user authorisation in the
	 * Dispatcher. This method MUST implement this authorisation check. If you
	 * do not need this in your platform, please always return true.
	 *
	 * @param   string  $component  The name of the component.
	 *
	 * @return  boolean  True to allow loading the component, false to halt loading
	 */
	public function authorizeAdmin($component)
	{
		return true;
	}

	/**
	 * Returns a user object.
	 *
	 * @param   integer  $id  The user ID to load. Skip or use null to retrieve
	 *                        the object for the currently logged in user.
	 *
	 * @return  JUser  The JUser object for the specified user
	 */
	public function getUser($id = null)
	{
		return null;
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
		return null;
	}

	/**
	 * This method will try retrieving a variable from the request (input) data.
	 * If it doesn't exist it will be loaded from the user state, typically
	 * stored in the session. If it doesn't exist there either, the $default
	 * value will be used. If $setUserState is set to true, the retrieved
	 * variable will be stored in the user session.
	 *
	 * @param   string    $key           The user state key for the variable
	 * @param   string    $request       The request variable name for the variable
	 * @param   FOFInput  $input         The FOFInput object with the request (input) data
	 * @param   mixed     $default       The default value. Default: null
	 * @param   string    $type          The filter type for the variable data. Default: none (no filtering)
	 * @param   boolean   $setUserState  Should I set the user state with the fetched value?
	 *
	 * @return  mixed  The value of the variable
	 */
	public function getUserStateFromRequest($key, $request, $input, $default = null, $type = 'none', $setUserState = true)
	{
		return $input->get($request, $default, $type);
	}

	/**
	 * Load plugins of a specific type. Obviously this seems to only be required
	 * in the Joomla! CMS.
	 *
	 * @param   string  $type  The type of the plugins to be loaded
	 *
	 * @return void
	 */
	public function importPlugin($type)
	{
	}

	/**
	 * Execute plugins (system-level triggers) and fetch back an array with
	 * their return values.
	 *
	 * @param   string  $event  The event (trigger) name, e.g. onBeforeScratchMyEar
	 * @param   array   $data   A hash array of data sent to the plugins as part of the trigger
	 *
	 * @return  array  A simple array containing the resutls of the plugins triggered
	 */
	public function runPlugins($event, $data)
	{
		return array();
	}

	/**
	 * Perform an ACL check. Please note that FOF uses by default the Joomla!
	 * CMS convention for ACL privileges, e.g core.edit for the edit privilege.
	 * If your platform uses different conventions you'll have to override the
	 * FOF defaults using fof.xml or by specialising the controller.
	 *
	 * @param   string  $action     The ACL privilege to check, e.g. core.edit
	 * @param   string  $assetname  The asset name to check, typically the component's name
	 *
	 * @return  boolean  True if the user is allowed this action
	 */
	public function authorise($action, $assetname)
	{
		return true;
	}

	/**
	 * Is this the administrative section of the component?
	 *
	 * @return  boolean
	 */
	public function isBackend()
	{
		return true;
	}

	/**
	 * Is this the public section of the component?
	 *
	 * @return  boolean
	 */
	public function isFrontend()
	{
		return true;
	}

	/**
	 * Is this a component running in a CLI application?
	 *
	 * @return  boolean
	 */
	public function isCli()
	{
		return true;
	}

	/**
	 * Is AJAX re-ordering supported? This is 100% Joomla!-CMS specific. All
	 * other platforms should return false and never ask why.
	 *
	 * @return  boolean
	 */
	public function supportsAjaxOrdering()
	{
		return true;
	}

	/**
	 * Performs a check between two versions. Use this function instead of PHP version_compare
	 * so we can mock it while testing
	 *
	 * @param   string  $version1  First version number
	 * @param   string  $version2  Second version number
	 * @param   string  $operator  Operator (see version_compare for valid operators)
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
	 * @param   string  $key      The key of the data to save
	 * @param   string  $content  The actual data to save
	 *
	 * @return  boolean  True on success
	 */
	public function setCache($key, $content)
	{
		return false;
	}

	/**
	 * Retrieves data from the cache. This is supposed to be used for system-side
	 * FOF data, not application data.
	 *
	 * @param   string  $key      The key of the data to retrieve
	 * @param   string  $default  The default value to return if the key is not found or the cache is not populated
	 *
	 * @return  string  The cached value
	 */
	public function getCache($key, $default = null)
	{
		return false;
	}

	/**
	 * Is the global FOF cache enabled?
	 *
	 * @return  boolean
	 */
	public function isGlobalFOFCacheEnabled()
	{
		return true;
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
		return false;
	}

	/**
	 * Logs in a user
	 *
	 * @param   array  $authInfo  authentification information
	 *
	 * @return  boolean  True on success
	 */
	public function loginUser($authInfo)
	{
		return true;
	}

	/**
	 * Logs out a user
	 *
	 * @return  boolean  True on success
	 */
	public function logoutUser()
	{
		return true;
	}

	/**
	 * Logs a deprecated practice. In Joomla! this results in the $message being output in the
	 * deprecated log file, found in your site's log directory.
	 *
	 * @param   string $message  The deprecated practice log message
	 *
	 * @return  void
	 */
	public function logDeprecated($message)
	{
		// The default implementation does nothing. Override this in your platform classes.
	}

	/**
	 * Returns the (internal) name of the platform implementation, e.g.
	 * "joomla", "foobar123" etc. This MUST be the last part of the platform
	 * class name. For example, if you have a plaform implementation class
	 * FOF30\Platform\Foobar you MUST return "foobar" (all lowercase).
	 *
	 * @return  string
	 *
	 * @since  2.1.2
	 */
	public function getPlatformName()
	{
		return $this->name;
	}

	/**
	 * Returns the version number string of the platform, e.g. "4.5.6". If
	 * implementation integrates with a CMS or a versioned foundation (e.g.
	 * a framework) it is advisable to return that version.
	 *
	 * @return  string
	 *
	 * @since  2.1.2
	 */
	public function getPlatformVersion()
	{
		return $this->version;
	}

	/**
	 * Returns the human readable platform name, e.g. "Joomla!", "Joomla!
	 * Framework", "Something Something Something Framework" etc.
	 *
	 * @return  string
	 *
	 * @since  2.1.2
	 */
	public function getPlatformHumanName()
	{
		return $this->humanReadableName;
	}
}
