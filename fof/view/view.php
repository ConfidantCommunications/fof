<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  view
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace FOF30\View;

use FOF30\Config\Provider;
use FOF30\Inflector\Inflector;
use FOF30\Input\Input;
use FOF30\Platform\Platform;
use FOF30\Render\RenderAbstract;
use FOF30\Utils\Object\Object;
use FOF30\Inflector\Inflector as FOFInflector;
use FOF30\Model\Model as FOFModel;

// Joomla! class inclusion
use JText, Exception;

// Protect from unauthorized access
defined('FOF30_INCLUDED') or die;

/**
 * FrameworkOnFramework View class. The View is the MVC component which gets the
 * raw data from a Model and renders it in a way that makes sense. The usual
 * rendering is HTML, but you can also output JSON, CSV, XML, or even media
 * (images, videos, ...) and documents (Word, PDF, Excel...).
 *
 * @package  FrameworkOnFramework
 * @since    1.0
 */
abstract class View extends Object
{
	/**
	 * The configuration array passed between MVC objects
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 * The name of this MVC object
	 *
	 * @var string
	 */
	protected $_name = null;

	/**
	 * The configuration parameters provider instance
	 *
	 * @var Provider
	 */
	protected $_configProvider = null;

	/**
	 * The config provider key for this MVC class
	 *
	 * @var string
	 */
	protected $_providerKey = null;

	/**
	 * The input object we're operating on
	 *
	 * @var Input
	 */
	protected $input = null;

	/**
	 * The vendor prefix for our class names
	 *
	 * @var string
	 */
	protected $_vendor = null;

	/**
	 * The application side we're located in
	 *
	 * @var string
	 */
	protected $_appSide = 'backend';

	/**
	 * The name of the component we belong to, with the com_prefix
	 *
	 * @var string
	 */
	protected $_component = null;

	/**
	 * Our component's name, without the com_prefix
	 *
	 * @var string
	 */
	protected $_bareComponent = null;

	/**
	 * The format specifier of the request (e.g. html, raw, json, form, csv, ...)
	 *
	 * @var string
	 */
	protected $_format = null;

	/**
	 * The task in the request. Not necessarily the one being executed in the context of this MVC class' triad.
	 *
	 * @var string
	 */
	protected $_task = null;

	/**
	 * Registered models
	 *
	 * @var    array
	 */
	protected $_models = array();

	/**
	 * The base path of the view
	 *
	 * @var    string
	 */
	protected $_basePath = null;

	/**
	 * The default model
	 *
	 * @var	string
	 */
	protected $_defaultModel = null;

	/**
	 * Layout name
	 *
	 * @var    string
	 */
	protected $_layout = 'default';

	/**
	 * Layout extension
	 *
	 * @var    string
	 */
	protected $_layoutExt = 'php';

	/**
	 * Layout template
	 *
	 * @var    string
	 */
	protected $_layoutTemplate = '_';

	/**
	 * The set of search directories for resources (templates)
	 *
	 * @var array
	 */
	protected $_path = array('template' => array(), 'helper' => array());

	/**
	 * The name of the default template source file.
	 *
	 * @var string
	 */
	protected $_template = null;

	/**
	 * The output of the template script.
	 *
	 * @var string
	 */
	protected $_output = null;

	/**
	 * The available renderer objects we can use to render views
	 *
	 * @var    array  Contains objects of the RenderAbstract class
	 */
	public static $renderers = array();

	/**
	 * The chosen renderer object
	 *
	 * @var    RenderAbstract
	 */
	protected $rendererObject = null;

	/**
	 * Should I run the pre-render step?
	 *
	 * @var    boolean
	 */
	protected $doPreRender = true;

	/**
	 * Should I run the post-render step?
	 *
	 * @var    boolean
	 */
	protected $doPostRender = true;

	/**
	 * Public constructor. Instantiates a FOFView object.
	 *
	 * @param   array  $config  The configuration data array
	 */
	public function __construct($config = array())
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
		$this->input = $input;

		// Load the configuration provider
		$this->_configProvider = new Provider();

		// Analyse our class name
		$classParts = $this->analyseClassName();

		// Get the component name
		$this->_component = $this->input->getCmd('option');
		$this->_component = $classParts['component'] ? $classParts['component'] : $this->_component;
		$this->_component = isset($config['option']) ? $config['option'] : $this->_component;
		$this->_component = empty($this->_component) ? 'com_foobar' : $this->_component;
		$config['option'] = $this->_component;

		// Get the bare component name
		$this->_bareComponent = (substr($this->_component, 0, 4) == 'com_') ? substr($this->_component, 4) : $this->_component;

		// Get the vendor name
		$this->_vendor = $classParts['vendor'] ? $classParts['vendor'] : 'Component';
		$this->_vendor = $this->_configProvider->get($this->_component . '.config.vendor', $this->_vendor);

		// Get the application side
		$this->_appSide = Platform::getInstance()->isBackend() ? 'Backend' : 'Frontend';
		$this->_appSide = isset($config['application_side']) ? $config['application_side'] : $this->_appSide;
		$config['application_side'] = $this->_appSide;

		// Get the class name
		$this->_name = $this->input->getCmd('view');
		$this->_name = $classParts['name'] ? $classParts['name'] : $this->_name;
		$this->_name = isset($config['name']) ? $config['name'] : $this->_name;
		$this->_name = empty($this->_name) ? 'invalid' : $this->_name;
		$config['name'] = $this->_name;

		// Get the format
		$this->_format = $classParts['specifier'];
		$this->_format = isset($config['format']) ? $config['format'] : $this->_format;
		$config['format'] = $this->_format;

		// Get the provider key
		$this->_providerKey = $this->_component . '.views.' . Inflector::singularize($this->_name);

		// Get the layout
		$this->_layout = $this->_configProvider->get($this->_providerKey . '.config.layout', $this->_layout);
		$this->_layout = $this->input->getCmd('layout', $this->_layout);
		$this->_layout = isset($config['layout']) ? $config['layout'] : $this->_layout;
		$config['layout'] = $this->_layout;

		// Get the task
		$this->_task = $this->input->getCmd('task', null);
		$this->_task = isset($config['task']) ? $config['task'] : $this->_task;
		$config['task'] = $this->_task;

		// Cache the config
		$this->config = $config;

		// Get the component directories
		$componentPaths = Platform::getInstance()->getComponentBaseDirs($this->_component);

		// Set a base path for use by the view
		if (array_key_exists('base_path', $config))
		{
			$this->_basePath = $config['base_path'];
		}
		else
		{
			$this->_basePath = $componentPaths['main'];
		}

		// Set the default template search path
		if (array_key_exists('template_path', $config))
		{
			// User-defined dirs
			$this->_setPath('template', $config['template_path']);
		}
		else
		{
			$altView = FOFInflector::isSingular($this->getName()) ? FOFInflector::pluralize($this->getName()) : FOFInflector::singularize($this->getName());
			$this->_setPath('template', $this->_basePath . '/views/' . $altView . '/tmpl');
			$this->_addPath('template', $this->_basePath . '/views/' . $this->getName() . '/tmpl');
		}

		// Set the default helper search path
		if (array_key_exists('helper_path', $config))
		{
			// User-defined dirs
			$this->_setPath('helper', $config['helper_path']);
		}
		else
		{
			$this->_setPath('helper', $this->_basePath . '/helpers');
		}

		// Set the layout
		if (array_key_exists('layout', $config))
		{
			$this->setLayout($config['layout']);
		}
		else
		{
			$this->setLayout('default');
		}

		$this->config = $config;

		if (!Platform::getInstance()->isCli())
		{
			$this->baseurl = Platform::getInstance()->URIbase(true);

			$fallback = Platform::getInstance()->getTemplateOverridePath($this->_component) . '/' . $this->getName();
			$this->_addPath('template', $fallback);
		}
	}

	/**
	 * Loads a template given any path. The path is in the format:
	 * [admin|site]:com_foobar/viewname/templatename
	 * e.g. admin:com_foobar/myview/default
	 *
	 * This function searches for Joomla! version override templates. For example,
	 * if you have run this under Joomla! 3.0 and you try to load
	 * admin:com_foobar/myview/default it will automatically search for the
	 * template files default.j30.php, default.j3.php and default.php, in this
	 * order.
	 *
	 * @param   string  $path         See above
	 * @param   array   $forceParams  A hash array of variables to be extracted in the local scope of the template file
	 *
	 * @return  boolean  False if loading failed
	 */
	public function loadAnyTemplate($path = '', $forceParams = array())
	{
		// Automatically check for a Joomla! version specific override
		$throwErrorIfNotFound = true;

		$suffixes = Platform::getInstance()->getTemplateSuffixes();

		foreach ($suffixes as $suffix)
		{
			if (substr($path, -strlen($suffix)) == $suffix)
			{
				$throwErrorIfNotFound = false;
				break;
			}
		}

		if ($throwErrorIfNotFound)
		{
			foreach ($suffixes as $suffix)
			{
				$result = $this->loadAnyTemplate($path . $suffix, $forceParams);

				if ($result !== false)
				{
					return $result;
				}
			}
		}

		$layoutTemplate = $this->getLayoutTemplate();

		// Parse the path
		$templateParts = $this->_parseTemplatePath($path);

		// Get the paths
		$componentPaths = Platform::getInstance()->getComponentBaseDirs($templateParts['component']);
		$templatePath   = Platform::getInstance()->getTemplateOverridePath($templateParts['component']);

		// Get the default paths
		$paths = array();
		$paths[] = $templatePath . '/' . $templateParts['view'];
		$paths[] = ($templateParts['admin'] ? $componentPaths['admin'] : $componentPaths['site']) . '/views/' . $templateParts['view'] . '/tmpl';

		if (isset($this->_path) || property_exists($this, '_path'))
		{
			$paths = array_merge($paths, $this->_path['template']);
		}
		elseif (isset($this->path) || property_exists($this, 'path'))
		{
			$paths = array_merge($paths, $this->path['template']);
		}

		// Look for a template override

		if (isset($layoutTemplate) && $layoutTemplate != '_' && $layoutTemplate != $templatePath)
		{
			$apath = array_shift($paths);
			array_unshift($paths, str_replace($templatePath, $layoutTemplate, $apath));
		}

		$filetofind = $templateParts['template'] . '.php';
        $filesystem = Platform::getInstance()->getFilesystemObject();

		$_tempFilePath = $filesystem->pathFind($paths, $filetofind);

		if ($_tempFilePath)
		{
			// Unset from local scope
			unset($template);
			unset($layoutTemplate);
			unset($paths);
			unset($path);
			unset($filetofind);

			// Never allow a 'this' property

			if (isset($this->this))
			{
				unset($this->this);
			}

			// Force parameters into scope

			if (!empty($forceParams))
			{
				extract($forceParams);
			}

			// Start capturing output into a buffer
			ob_start();

			// Include the requested template filename in the local scope (this will execute the view logic).
			include $_tempFilePath;

			// Done with the requested template; get the buffer and clear it.
			$this->_output = ob_get_contents();
			ob_end_clean();

			return $this->_output;
		}
		else
		{
			if ($throwErrorIfNotFound)
			{
				return new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $path), 500);
			}

			return false;
		}
	}

	/**
	 * Overrides the default method to execute and display a template script.
	 * Instead of loadTemplate is uses loadAnyTemplate which allows for automatic
	 * Joomla! version overrides. A little slice of awesome pie!
	 *
	 * @param   string  $tpl  The name of the template file to parse
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		Platform::getInstance()->setErrorHandling(E_ALL, 'ignore');

		$result = $this->loadTemplate($tpl);

		if ($result instanceof Exception)
		{
			Platform::getInstance()->raiseError($result->getCode(), $result->getMessage());

			return $result;
		}

		echo $result;
	}

	/**
	 * Assigns variables to the view script via differing strategies.
	 *
	 * This method is overloaded; you can assign all the properties of
	 * an object, an associative array, or a single value by name.
	 *
	 * You are not allowed to set variables that begin with an underscore;
	 * these are either private properties for FOFView or private variables
	 * within the template script itself.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @deprecated  13.3 Use native PHP syntax.
	 */
	public function assign()
	{
		Platform::getInstance()->logDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated. Use native PHP syntax.');

		// Get the arguments; there may be 1 or 2.
		$arg0 = @func_get_arg(0);
		$arg1 = @func_get_arg(1);

		// Assign by object

		if (is_object($arg0))
		{
			// Assign public properties
			foreach (get_object_vars($arg0) as $key => $val)
			{
				if (substr($key, 0, 1) != '_')
				{
					$this->$key = $val;
				}
			}

			return true;
		}

		// Assign by associative array

		if (is_array($arg0))
		{
			foreach ($arg0 as $key => $val)
			{
				if (substr($key, 0, 1) != '_')
				{
					$this->$key = $val;
				}
			}

			return true;
		}

		// Assign by string name and mixed value. We use array_key_exists() instead of isset()
		// because isset() fails if the value is set to null.

		if (is_string($arg0) && substr($arg0, 0, 1) != '_' && func_num_args() > 1)
		{
			$this->$arg0 = $arg1;

			return true;
		}

		// $arg0 was not object, array, or string.
		return false;
	}

	/**
	 * Assign variable for the view (by reference).
	 *
	 * You are not allowed to set variables that begin with an underscore;
	 * these are either private properties for FOFView or private variables
	 * within the template script itself.
	 *
	 * @param   string  $key   The name for the reference in the view.
	 * @param   mixed   &$val  The referenced variable.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @deprecated  13.3  Use native PHP syntax.
	 */
	public function assignRef($key, &$val)
	{
		Platform::getInstance()->logDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated. Use native PHP syntax.');

		if (is_string($key) && substr($key, 0, 1) != '_')
		{
			$this->$key = &$val;

			return true;
		}

		return false;
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * If escaping mechanism is either htmlspecialchars or htmlentities, uses
	 * {@link $_encoding} setting.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  mixed  The escaped value.
	 */
	public function escape($var)
	{
		return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Method to get data from a registered model or a property of the view
	 *
	 * @param   string  $property  The name of the method to call on the model or the property to get
	 * @param   string  $default   The name of the model to reference or the default value [optional]
	 *
	 * @return  mixed  The return value of the method
	 */
	public function get($property, $default = null)
	{
		// If $model is null we use the default model
		if (is_null($default))
		{
			$model = $this->_defaultModel;
		}
		else
		{
			$model = strtolower($default);
		}

		// First check to make sure the model requested exists
		if (isset($this->_models[$model]))
		{
			// Model exists, let's build the method name
			$method = 'get' . ucfirst($property);

			// Does the method exist?
			if (method_exists($this->_models[$model], $method))
			{
				// The method exists, let's call it and return what we get
				$result = $this->_models[$model]->$method();

				return $result;
			}
		}

		// Degrade to FOFUtilsObject::get
		$result = parent::get($property, $default);

		return $result;
	}

	/**
	 * Method to get the model object
	 *
	 * @param   string  $name  The name of the model (optional)
	 *
	 * @return  mixed  FOFModel object
	 */
	public function getModel($name = null)
	{
		if ($name === null)
		{
			$name = $this->_defaultModel;
		}

		return $this->_models[strtolower($name)];
	}

	/**
	 * Get the layout.
	 *
	 * @return  string  The layout name
	 */
	public function getLayout()
	{
		return $this->_layout;
	}

	/**
	 * Get the layout template.
	 *
	 * @return  string  The layout template name
	 */
	public function getLayoutTemplate()
	{
		return $this->_layoutTemplate;
	}

	/**
	 * Method to get the view name
	 *
	 * The model name by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the model
	 */
	public function getName()
	{
		if (empty($this->_name))
		{
			$classname = get_class($this);
			$viewpos = strpos($classname, 'View');

			if ($viewpos === false)
			{
				throw new Exception(JText::_('JLIB_APPLICATION_ERROR_VIEW_GET_NAME'), 500);
			}

			$this->_name = strtolower(substr($classname, $viewpos + 4));
		}

		return $this->_name;
	}

	/**
	 * Method to add a model to the view.
	 *
	 * @param   FOFModel  $model    The model to add to the view.
     * @param   boolean   $default  Is this the default model?
     * @param   String    $name     optional index name to store the model
	 *
	 * @return  object   The added model.
	 */
	public function setModel($model, $default = false, $name = null)
	{
		if (is_null($name))
		{
			$name = $model->getName();
		}

		$name = strtolower($name);

		$this->_models[$name] = $model;

		if ($default)
		{
			$this->_defaultModel = $name;
		}

		return $model;
	}

	/**
	 * Sets the layout name to use
	 *
	 * @param   string  $layout  The layout name or a string in format <template>:<layout file>
	 *
	 * @return  string  Previous value.
	 */
	public function setLayout($layout)
	{
		$previous = $this->_layout;

		if (strpos($layout, ':') === false)
		{
			$this->_layout = $layout;
		}
		else
		{
			// Convert parameter to array based on :
			$temp = explode(':', $layout);
			$this->_layout = $temp[1];

			// Set layout template
			$this->_layoutTemplate = $temp[0];
		}

		return $previous;
	}

	/**
	 * Allows a different extension for the layout files to be used
	 *
	 * @param   string  $value  The extension.
	 *
	 * @return  string   Previous value
	 */
	public function setLayoutExt($value)
	{
		$previous = $this->_layoutExt;

		if ($value = preg_replace('#[^A-Za-z0-9]#', '', trim($value)))
		{
			$this->_layoutExt = $value;
		}

		return $previous;
	}

	/**
	 * Adds to the stack of view script paths in LIFO order.
	 *
	 * @param   mixed  $path  A directory path or an array of paths.
	 *
	 * @return  void
	 */
	public function addTemplatePath($path)
	{
		$this->_addPath('template', $path);
	}

	/**
	 * Adds to the stack of helper script paths in LIFO order.
	 *
	 * @param   mixed  $path  A directory path or an array of paths.
	 *
	 * @return  void
	 */
	public function addHelperPath($path)
	{
		$this->_addPath('helper', $path);
	}

	/**
	 * Overrides the built-in loadTemplate function with an FOF-specific one.
	 * Our overriden function uses loadAnyTemplate to provide smarter view
	 * template loading.
	 *
	 * @param   string   $tpl     The name of the template file to parse
	 * @param   boolean  $strict  Should we use strict naming, i.e. force a non-empty $tpl?
	 *
	 * @return  mixed  A string if successful, otherwise a JError object
	 */
	public function loadTemplate($tpl = null, $strict = false)
	{
		$paths = Platform::getInstance()->getViewTemplatePaths(
			$this->input->getCmd('option', ''),
			$this->input->getCmd('view', ''),
			$this->getLayout(),
			$tpl,
			$strict
		);

		foreach ($paths as $path)
		{
			$result = $this->loadAnyTemplate($path);

			if (!($result instanceof Exception))
			{
				break;
			}
		}

		if ($result instanceof Exception)
		{
			Platform::getInstance()->raiseError($result->getCode(), $result->getMessage());
		}

		return $result;
	}

	/**
	 * Parses a template path in the form of admin:/component/view/layout or
	 * site:/component/view/layout to an array which can be used by
	 * loadAnyTemplate to locate and load the view template file.
	 *
	 * @param   string  $path  The template path to parse
	 *
	 * @return  array  A hash array with the parsed path parts
	 */
	private function _parseTemplatePath($path = '')
	{
		$parts = array(
			'admin'		 => 0,
			'component'	 => $this->config['option'],
			'view'		 => $this->config['view'],
			'template'	 => 'default'
		);

		if (substr($path, 0, 6) == 'admin:')
		{
			$parts['admin'] = 1;
			$path = substr($path, 6);
		}
		elseif (substr($path, 0, 5) == 'site:')
		{
			$path = substr($path, 5);
		}

		if (empty($path))
		{
			return array();
		}

		$pathparts = explode('/', $path, 3);

		switch (count($pathparts))
		{
			case 3:
				$parts['component'] = array_shift($pathparts);

			case 2:
				$parts['view'] = array_shift($pathparts);

			case 1:
				$parts['template'] = array_shift($pathparts);
				break;
		}

		return $parts;
	}

	/**
	 * Get the renderer object for this view
	 *
	 * @return  RenderAbstract
	 */
	public function &getRenderer()
	{
		if (!($this->rendererObject instanceof RenderAbstract))
		{
			$this->rendererObject = $this->findRenderer();
		}

		return $this->rendererObject;
	}

	/**
	 * Sets the renderer object for this view
	 *
	 * @param   RenderAbstract  &$renderer  The render class to use
	 *
	 * @return  void
	 */
	public function setRenderer(RenderAbstract &$renderer)
	{
		$this->rendererObject = $renderer;
	}

	/**
	 * Finds a suitable renderer
	 *
	 * @return  RenderAbstract
	 */
	protected function findRenderer()
	{
        $filesystem     = Platform::getInstance()->getFilesystemObject();

		// Try loading the stock renderers shipped with FOF

		if (empty(self::$renderers) || !class_exists('\\FOF30\\Render\\Joomla', false))
		{
			$path = dirname(__FILE__) . '/../render/';
			$renderFiles = $filesystem->folderFiles($path, '.php');

			if (!empty($renderFiles))
			{
				foreach ($renderFiles as $filename)
				{
					if ($filename == 'renderabstract.php')
					{
						continue;
					}

					@include_once $path . '/' . $filename;

					$camel = FOFInflector::camelize($filename);
					$className = '\\FOF30\\Render\\' . ucfirst(FOFInflector::getPart($camel, 0));
					$o = new $className;

					self::registerRenderer($o);
				}
			}
		}

		// Try to detect the most suitable renderer
		$o = null;
		$priority = 0;

		if (!empty(self::$renderers))
		{
			/** @var RenderAbstract $r */
			foreach (self::$renderers as $r)
			{
				$info = $r->getInformation();

				if (!$info->enabled)
				{
					continue;
				}

				if ($info->priority > $priority)
				{
					$priority = $info->priority;
					$o = $r;
				}
			}
		}

		// Return the current renderer
		return $o;
	}

	/**
	 * Registers a renderer object with the view
	 *
	 * @param   RenderAbstract  &$renderer  The render object to register
	 *
	 * @return  void
	 */
	public static function registerRenderer(RenderAbstract &$renderer)
	{
		self::$renderers[] = $renderer;
	}

	/**
	 * Sets the pre-render flag
	 *
	 * @param   boolean  $value  True to enable the pre-render step
	 *
	 * @return  void
	 */
	public function setPreRender($value)
	{
		$this->doPreRender = $value;
	}

	/**
	 * Sets the post-render flag
	 *
	 * @param   boolean  $value  True to enable the post-render step
	 *
	 * @return  void
	 */
	public function setPostRender($value)
	{
		$this->doPostRender = $value;
	}

	/**
	 * Load a helper file
	 *
	 * @param   string  $hlp  The name of the helper source file automatically searches the helper paths and compiles as needed.
	 *
	 * @return  void
	 */
	public function loadHelper($hlp = null)
	{
		// Clean the file name
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $hlp);

		// Load the template script using the default Joomla! features
        $filesystem = Platform::getInstance()->getFilesystemObject();

		$helper = $filesystem->pathFind($this->_path['helper'], $this->_createFileName('helper', array('name' => $file)));

		if ($helper == false)
		{
			$componentPaths = Platform::getInstance()->getComponentBaseDirs($this->config['option']);
			$path = $componentPaths['main'] . '/helpers';
			$helper = $filesystem->pathFind($path, $this->_createFileName('helper', array('name' => $file)));

			if ($helper == false)
			{
				$path = $path = $componentPaths['alt'] . '/helpers';
				$helper = $filesystem->pathFind($path, $this->_createFileName('helper', array('name' => $file)));
			}
		}

		if ($helper != false)
		{
			// Include the requested template filename in the local scope
			include_once $helper;
		}
	}

	/**
	 * Returns the view's option (component name) and view name in an
	 * associative array.
	 *
	 * @return  array
	 */
	public function getViewOptionAndName()
	{
		return array(
			'option' => $this->config['option'],
			'view'	 => $this->config['view'],
		);
	}

	/**
	 * Sets an entire array of search paths for templates or resources.
	 *
	 * @param   string  $type  The type of path to set, typically 'template'.
	 * @param   mixed   $path  The new search path, or an array of search paths.  If null or false, resets to the current directory only.
	 *
	 * @return  void
	 */
	protected function _setPath($type, $path)
	{
		// Clear out the prior search dirs
		$this->_path[$type] = array();

		// Actually add the user-specified directories
		$this->_addPath($type, $path);

		// Always add the fallback directories as last resort
		switch (strtolower($type))
		{
			case 'template':
				// Set the alternative template search dir

				if (!Platform::getInstance()->isCli())
				{
					$fallback = Platform::getInstance()->getTemplateOverridePath($this->input->getCmd('option', '')) . '/' . $this->getName();
					$this->_addPath('template', $fallback);
				}

				break;
		}
	}

	/**
	 * Adds to the search path for templates and resources.
	 *
	 * @param   string  $type  The type of path to add.
	 * @param   mixed   $path  The directory or stream, or an array of either, to search.
	 *
	 * @return  void
	 */
	protected function _addPath($type, $path)
	{
		// Just force to array
		settype($path, 'array');

		// Loop through the path directories
		foreach ($path as $dir)
		{
			// No surrounding spaces allowed!
			$dir = trim($dir);

			// Add trailing separators as needed
			if (substr($dir, -1) != DIRECTORY_SEPARATOR)
			{
				// Directory
				$dir .= DIRECTORY_SEPARATOR;
			}

			// Add to the top of the search dirs
			array_unshift($this->_path[$type], $dir);
		}
	}

	/**
	 * Create the filename for a resource
	 *
	 * @param   string  $type   The resource type to create the filename for
	 * @param   array   $parts  An associative array of filename information
	 *
	 * @return  string  The filename
	 */
	protected function _createFileName($type, $parts = array())
	{
		switch ($type)
		{
			case 'template':
				$filename = strtolower($parts['name']) . '.' . $this->_layoutExt;
				break;

			default:
				$filename = strtolower($parts['name']) . '.php';
				break;
		}

		return $filename;
	}

	/**
	 * Analyses an MVC class name to the parts that compose it (vendor, side, component, type, name and specifier)
	 *
	 * @param string $className The class name to analyse. Leave null to use our own class.
	 *
	 * @return array
	 */
	protected function analyseClassName($className = null)
	{
		$ret = array(
			'vendor'	=> null,
			'side'		=> null,
			'component'	=> null,
			'type'		=> null,
			'name'		=> null,
			'specifier'	=> null
		);

		if (is_null($className))
		{
			$className = get_class($this);
		}

		if (strstr($className, '\\'))
		{
			// Remove leading and trailing slashes
			$className = trim($className, '\\');

			// Explode to parts
			$parts = explode('\\', $className);

			// If the parts are too few we have a default FOF class, we can't analyse it
			if (count($parts) < 5)
			{
				return $ret;
			}

			$ret['vendor'] = strtolower($parts[0]);
			$ret['side'] = strtolower($parts[1]);
			$ret['component'] = 'com_' . strtolower($parts[2]);
			$ret['type'] = strtolower($parts[3]);
			$ret['name'] = strtolower($parts[4]);

			if (count($parts) > 5)
			{
				$ret['specifier'] = strtolower($parts[5]);
			}

			return $ret;
		}

		// Fallback to legacy class names
		$classNameParts = Inflector::explode($className);

		if (count($classNameParts) == 3)
		{
			$ret['component'] = "com_" . $classNameParts[0];
			$ret['type'] = $classNameParts[1];
			$ret['name'] = $classNameParts[2];
		}

		return $ret;
	}
}
