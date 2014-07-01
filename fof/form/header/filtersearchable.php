<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace FOF30\Form\Header;

use FOF30\Form\Header\Fieldsearchable as FOFFormHeaderFieldsearchable;
use FOF30\Form\Header as FOFFormHeader;

// Joomla! class inclusion
use JText, JHtml, JFactory;

// Protect from unauthorized access
defined('FOF30_INCLUDED') or die;

/**
 * Generic filter, text box entry with optional buttons
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class Filtersearchable extends FOFFormHeaderFieldsearchable
{
	/**
	 * Get the header
	 *
	 * @return  string  The header HTML
	 */
	protected function getHeader()
	{
		return '';
	}
}
