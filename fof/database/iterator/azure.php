<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  database
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is adapted from the Joomla! Platform. It is used to iterate a database cursor returning FOFTable objects
 * instead of plain stdClass objects
 */

namespace FOF30\Database\Iterator;

// Protect from unauthorized access
defined('FOF30_INCLUDED') or die;

/**
 * SQL azure database iterator.
 */
class Azure extends Sqlsrv
{
}
