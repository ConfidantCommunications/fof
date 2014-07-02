<?php
/**
 * @package    FrameworkOnFramework.UnitTest
 * @subpackage Core
 *
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use FOF30\Integration\Joomla\Platform as FOFIntegrationJoomlaPlatform;

class FtestPlatformJoomla extends FOFIntegrationJoomlaPlatform
{
	/**
	 * @var		boolean   Should this platform instance report running under CLI mode?
	 */
	public static $isCli = false;

	/**
	 * @var		boolean   Should this platform instance report running under Joomla! backend?
	 */
	public static $isAdmin = false;

	protected function isCliAdmin()
	{
		return array(self::$isCli, self::$isAdmin);
	}
}