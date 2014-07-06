<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  utils
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace FOF30\Utils\Mvc;

use FOF30\Config\Provider;
use FOF30\Inflector\Inflector;
use FOF30\Input\Input;
use FOF30\Platform\Platform;
use FOF30\Utils\Object\Object;

defined('FOF30_INCLUDED') or die;

abstract class Base extends Object
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
	protected $name = null;

	/**
	 * The configuration parameters provider instance
	 *
	 * @var Provider
	 */
	protected $configProvider = null;

	/**
	 * The config provider key for this MVC class
	 *
	 * @var string
	 */
	protected $providerKey = null;

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
	protected $vendor = null;

	/**
	 * The application side we're located in
	 *
	 * @var string
	 */
	protected $appSide = 'backend';

	/**
	 * The name of the component we belong to, with the com_prefix
	 *
	 * @var string
	 */
	protected $component = null;

	/**
	 * Our component's name, without the com_prefix
	 *
	 * @var string
	 */
	protected $bareComponent = null;

	/**
	 * The format specifier of the request (e.g. html, raw, json, form, csv, ...)
	 *
	 * @var string
	 */
	protected $format = null;

	/**
	 * The layout of the view set in the request (e.g. default)
	 *
	 * @var string
	 */
	protected $layout = null;

	/**
	 * The task in the request. Not necessarily the one being executed in the context of this MVC class' triad.
	 *
	 * @var string
	 */
	protected $task = null;

	public function __construct($config = array())
	{
		parent::__construct();

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
		$this->configProvider = new Provider();

		// Analyse our class name
		$classParts = $this->analyseClassName();

		// Get the component name
		$this->component = $this->input->getCmd('option');
		$this->component = $classParts['component'] ? $classParts['component'] : $this->component;
		$this->component = isset($config['option']) ? $config['option'] : $this->component;
		$this->component = empty($this->component) ? 'com_foobar' : $this->component;
		$config['option'] = $this->component;

		// Get the bare component name
		$this->bareComponent = (substr($this->component, 0, 4) == 'com_') ? substr($this->component, 4) : $this->component;

		// Get the vendor name
		$this->vendor = $classParts['vendor'] ? $classParts['vendor'] : 'Component';
		$this->vendor = $this->configProvider->get($this->component . '.config.vendor', $this->vendor);

		// Get the application side
		$this->appSide = Platform::getInstance()->isBackend() ? 'Backend' : 'Frontend';
		$this->appSide = isset($config['application_side']) ? $config['application_side'] : $this->appSide;
		$config['application_side'] = $this->appSide;

		// Get the class name
		$this->name = $this->input->getCmd('view');
		$this->name = $classParts['name'] ? $classParts['name'] : $this->name;
		$this->name = isset($config['view']) ? $config['view'] : $this->name;
		$this->name = isset($config['name']) ? $config['name'] : $this->name;
		$config['name'] = $this->name;

		// Get the format
		$this->format = $classParts['specifier'];
		$this->format = isset($config['format']) ? $config['format'] : $this->format;
		$config['format'] = $this->format;

		// Get the provider key
		$this->providerKey = $this->component . '.views.' . Inflector::singularize($this->name);

		// Get the layout
		$this->layout = $this->configProvider->get($this->providerKey . '.config.layout', $this->layout);
		$this->layout = $this->input->getCmd('layout', $this->layout);
		$this->layout = isset($config['layout']) ? $config['layout'] : $this->layout;
		$config['layout'] = $this->layout;

		// Get the task
		$this->task = $this->input->getCmd('task', null);
		$this->task = isset($config['task']) ? $config['task'] : $this->task;
		$config['task'] = $this->task;

		// Cache the config
		$this->config = $config;
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