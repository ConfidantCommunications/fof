<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace FOF30\Form\Header;

// Joomla! class inclusion
use JText;

// Protect from unauthorized access
defined('FOF30_INCLUDED') or die;

/**
 * Row selection checkbox
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class Rowselect extends F0FFormHeader
{
	/**
	 * Get the header
	 *
	 * @return  string  The header HTML
	 */
	protected function getHeader()
	{
		return '<input type="checkbox" name="checkall-toggle" value="" title="'
			. JText::_('JGLOBAL_CHECK_ALL')
			. '" onclick="Joomla.checkAll(this)" />';
	}
}
