<?php
/**
 *  @package     FrameworkOnFramework
 *  @subpackage  include
 *  @copyright   Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license     GNU General Public License version 2, or later
 *
 *  Initializes F0F
 */

use FOF30\Autoloader\Autoloader as FOFAutoloader, FOF30\Platform;

defined('_JEXEC') or die();

if (!defined('FOF30_INCLUDED'))
{
    define('FOF30_INCLUDED', '##VERSION##');

	// Register the F0F autoloader
    require_once __DIR__ . '/autoloader/autoloader.php';
	FOFAutoloader::getInstance()->addMap('FOF30\\', array(__DIR__));
	FOFAutoloader::getInstance()->register();

	// Register a debug log
	if (defined('JDEBUG') && JDEBUG)
	{
		FOF30\Platform\Platform::getInstance()->logAddLogger('fof30.log.php');
	}
}