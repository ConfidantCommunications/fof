<?php
/**
 * @package    FrameworkOnFramework.UnitTest
 * @subpackage Core
 *
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

class FtestPlatformJoomla extends FOF30\Platform\Platform
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