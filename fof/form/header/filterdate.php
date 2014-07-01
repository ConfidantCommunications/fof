<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace FOF30\Form\Header;

use FOF30\Form\Header\Fielddate as FOFFormHeaderFielddate;
use FOF30\Form\Header as FOFFormHeader;

// Joomla! class inclusion
use JText, JHtml, JFactory;

/**
 * Generic filter, text box entry with calendar button
 *
 * @package  FrameworkOnFramework
 * @since    2.3.3
 */
class Filterdate extends FOFFormHeaderFielddate
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
