<?php
/**
 * @package		fof
 * @copyright	2014 Nicholas K. Dionysopoulos / Akeeba Ltd 
 * @license		GNU GPL version 3 or later
 */

namespace FOF30\Utils\Filefinder;


use FOF30\Inflector\Inflector;
use FOF30\Platform\Platform;

abstract class Filefinder
{
	/**
	 * Cache which maps class finding options to the already determined filename and class name
	 *
	 * @var array
	 */
	static $classCache = array();

	/**
	 * Determines the class name and the file location for a component's MVC classes (Controllers, Models, Tables and
	 * Views). If the component-specific file/class is not found it will return the default FOF class name for the MVC
	 * class type requested, e.g. \FOF30\Controller\Controller for the default Controller.
	 *
	 * The return is a hash array with keys "file" and "class". The "file" key contains the filename where the class
	 * is found. You do not need to include the file, FOF has already done that for you. It's there mostly for debugging
	 * purposes, i.e. to help you understand which file is being used. You simply need to instantiate the class
	 * specified in the "class" key.
	 *
	 * This function is autoloader-aware. If you have a class autoloader which finds the classes of your component
	 * automatically the "file" key will remain empty. We recommend using a PSR-4 autoloader, e.g. the one shipped with
	 * FOF (FOF30\Autoloader\Autoloader), to autoload your component's classes. It's faster and more flexible than
	 * relying on this helper.
	 *
	 * The results are cached to speed up the execution of FOF. You can clear it by calling the clearClassCache static
	 * method of this class.
	 *
	 * @param   string   $vendor    The optional vendor name of the component. If none is specified "Component" is
	 *                              assumed.
	 * @param   string  $option     The component name, with or without the com_ prefix (com_foobar or foobar)
	 * @param   string  $type       The class type. One of Controller, Model, Table, View, Dispatcher, Toolbar
	 * @param   string  $name       The specific name of the class, e.g. "Bar" for \VendorName\OptionName\Controller\Bar
	 *                              For Dispatcher and Toolbar this must be NULL.
	 * @param   string  $specifier  OPTIONAL. A specifier used for View files, appended to the class name. Typically one
	 *                              of Html, Json, Csv or Raw.
	 * @param   array   $addPaths   Additional paths where to look for class files. These take precedence over defaults.
	 *
	 * @return  array  A hash array with the keys "file" containing the filename and "class" containing the class name.
	 */
	public static function getClassFile($vendor, $option, $type, $name, $specifier = null, array $addPaths = array())
	{
		// Normalise component name
		$option = strtolower($option);

		if (substr($option, 0, 4) == 'com_')
		{
			$option = substr($option, 4);
		}

		// Normalise the vendor
		$vendor = empty($vendor) ? 'Component' : $vendor;

		// Normalise the type
		$type = Inflector::singularize($type);

		// Get the class signature
		$signature = implode('_', array($vendor, $option, $type, $name));
		$signature = empty($specifier) ? $signature : ($signature . '_' . $specifier);
		$signature = strtolower($signature);

		// If the class signature exists in the cache, return the cached result
		if (isset(self::$classCache[$signature]))
		{
			return self::$classCache[$signature];
		}

		// Normalise case
		$vendor = ucfirst($vendor);
		$option = ucfirst($option);
		$type = ucfirst($type);
		$name = ucfirst($name);
		$specifier = ucfirst($specifier);

		// Get the default FOF class name
		$defaultClass = '\\FOF30\\' . $type . '\\' . $type . (empty($specifier) ? '' : ('\\' . $specifier));

		if (!empty($specifier) && !class_exists($defaultClass, true))
		{
			$defaultClass = '\\FOF30\\' . $type . '\\' . $type;
		}

		// Initialise
		self::$classCache[$signature] = array(
			'file'	=> null,
			'class'	=> $defaultClass,
		);

		// Get the front-end / back-end specifiers
		$thisSide = 'Backend';
		$otherSide = 'Frontend';

		if (Platform::getInstance()->isFrontend())
		{
			$thisSide = 'Frontend';
			$otherSide = 'Backend';
		}

		// Get the alternate pluralised form
		$altName = '';

		if (!empty($name))
		{
			$altName = Inflector::isPlural($name) ? Inflector::singularize($name) : Inflector::pluralize($name);
		}

		// Get the pluralised type form
		$altType = empty($name) ? $type : Inflector::pluralize($type);

		// Get all of the possible class names
		$classNames = array(
			implode('\\', array($vendor, $option, $thisSide, $type, $name)),
			implode('\\', array($vendor, $option, $thisSide, $type, $altName)),

			implode('\\', array($vendor, $option, $otherSide, $type, $name)),
			implode('\\', array($vendor, $option, $otherSide, $type, $altName)),

			implode('\\', array($option, $thisSide, $type, $name)),
			implode('\\', array($option, $thisSide, $type, $altName)),

			implode('\\', array($option, $otherSide, $type, $name)),
			implode('\\', array($option, $otherSide, $type, $altName)),

			implode('', array($option, $type, $name)),
			implode('', array($option, $type, $altName)),
		);

		// Get all the possible component-default class names
		$defaultClassNames = array(
			implode('\\', array($vendor, $option, $thisSide, $type, 'Default')),
			implode('\\', array($vendor, $option, $otherSide, $type, 'Default')),
			implode('\\', array($option, $thisSide, $type, 'Default')),
			implode('\\', array($option, $otherSide, $type, 'Default')),
			implode('', array($option, $type, 'Default')),
		);

		// If the name is empty (Dispatcher, Toolbar) we need to do some further filtering
		if (empty($name))
		{
			$classNames = array_map(function ($c) {
				return rtrim($c, '\\');
			}, $classNames);

			$classNames = array_unique($classNames);

			$defaultClassNames = array_map(function ($c) {
				return rtrim($c, '\\');
			}, $defaultClassNames);

			$defaultClassNames = array_unique($defaultClassNames);
		}

		// Add the optional specifier if necessary
		if (!empty($specifier))
		{
			$temp = array();

			foreach ($classNames as $className)
			{
				$temp[] = $className . '\\' . $specifier;
				$temp[] = $className;
			}

			$classNames = $temp;
		}

		// First check if the class autoloader can find the class without us having to explicitly look for it
		foreach ($classNames as $className)
		{
			if (class_exists($className, true))
			{
				self::$classCache[$signature] = array(
					'file'  => null,
					'class' => $className,
				);

				return self::$classCache[$signature];
			}
		}

		// Get the component directories
		$componentName = 'com_' . strtolower($option);
		$componentDirs = Platform::getInstance()->getComponentBaseDirs($componentName);
		$mainDir = $componentDirs['main'];
		$altDir = $componentDirs['alt'];

		// Determine all the possible directories where the class file can be located
		$filePaths = array(
			implode('/', array($mainDir, $type, $name . '.php')),
			implode('/', array($mainDir, $type, strtolower($name) . '.php')),
			implode('/', array($mainDir, $type, $altName . '.php')),
			implode('/', array($mainDir, $type, strtolower($altName) . '.php')),

			implode('/', array($mainDir, $altType, $name . '.php')),
			implode('/', array($mainDir, $altType, strtolower($name) . '.php')),
			implode('/', array($mainDir, $altType, $altName . '.php')),
			implode('/', array($mainDir, $altType, strtolower($altName) . '.php')),

			implode('/', array($altDir, $type, $name . '.php')),
			implode('/', array($altDir, $type, strtolower($name) . '.php')),
			implode('/', array($altDir, $type, $altName . '.php')),
			implode('/', array($altDir, $type, strtolower($altName) . '.php')),

			implode('/', array($altDir, $altType, $name . '.php')),
			implode('/', array($altDir, $altType, strtolower($name) . '.php')),
			implode('/', array($altDir, $altType, $altName . '.php')),
			implode('/', array($altDir, $altType, strtolower($altName) . '.php')),
		);

		// Put the additional paths in the beginning of the array
		if (is_array($addPaths))
		{
			$extraFiles = array();

			foreach($addPaths as $path)
			{
				$extraFiles[] = implode('/', array($path, $name . '.php'));
				$extraFiles[] = implode('/', array($path, strtolower($name) . '.php'));
				$extraFiles[] = implode('/', array($path, $altName . '.php'));
				$extraFiles[] = implode('/', array($path, strtolower($altName) . '.php'));
			}

			$filePaths = array_merge($extraFiles, $filePaths);
		}

		// View class files are special. Instead of looking for something like views/foobar.php we have to first look
		// for views/foobar/view.php.
		if ($type == 'View')
		{
			$newFiles = array();

			foreach ($filePaths as $path)
			{
				$baseName = substr($path, 0, -4) . '.';

				$newFiles[] = $baseName . '/View.php';
				$newFiles[] = $baseName . '/view.php';
			}

			$filePaths = array_merge($newFiles, $filePaths);
		}

		// If we have a specifier make sure we are looking for files with a specifier in their name before plain
		// filenames, e.g. view.html.php before view.php
		if (!empty($specifier))
		{
			$newFiles = array();
			$lowercaseSpecifier = strtolower($specifier);

			foreach ($filePaths as $path)
			{
				$baseName = substr($path, 0, -4);

				$newFiles[] = $baseName . '.' . $specifier . '.php';
				$newFiles[] = $baseName . '.' . $lowercaseSpecifier . '.php';

				// Views are special. The basename is something like .../views/foobar/view.php. Well, we have to
				// look for View.php, view.php, View.Html.php, view.html.php, Html.php and html.php inside that
				// directory. View/view.php are already handled, so we need the other FOUR cases. The view.html.php
				// and View.Html.php are already handled so we need to handle the html.php and Html.php here.
				if ($type == 'View')
				{
					// Remove the View part from the base name
					$viewBaseName = substr($baseName, 0, -4);

					$newFiles[] = $viewBaseName . $specifier . '.php';
					$newFiles[] = $viewBaseName . $lowercaseSpecifier . '.php';
				}
			}

			$filePaths = array_merge($newFiles, $filePaths);
		}

		// If the name is empty (Dispatcher, Toolbar) we need to do some further filtering
		if (empty($name))
		{
			$filePaths = array_map(function ($path) {
				return str_replace('/.', '.', $path);
			}, $filePaths);
		}

		// Keep only unique file path names
		$filePaths = array_unique($filePaths);

		// Get the filesystem object
		$fs = Platform::getInstance()->getFilesystemObject();

		// Check if each file exists, load it and check if it contains the class we are looking for
		foreach ($filePaths as $filePath)
		{
			if (!$fs->fileExists($filePath))
			{
				continue;
			}

			if (!@include_once($filePath))
			{
				continue;
			}

			foreach ($classNames as $className)
			{
				if (class_exists($className, false))
				{
					self::$classCache[$signature] = array(
						'file'	=> $filePath,
						'class'	=> $className,
					);

					return self::$classCache[$signature];
				}
			}
		}

		// Finally check for component-default classes
		foreach ($defaultClassNames as $className)
		{
			if (class_exists($className, true))
			{
				self::$classCache[$signature] = array(
					'file'  => null,
					'class' => $className,
				);

				return self::$classCache[$signature];
			}
		}

		// If you have reached this point, we haven't found the class and we're returning the FOF-default value instead
		return self::$classCache[$signature];
	}

	/**
	 * Clears the class cache. Used in Unit Testing.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	public static function clearClassCache()
	{
		self::$classCache = array();
	}
} 