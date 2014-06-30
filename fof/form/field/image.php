<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace FOF30\Form\Field;

use FOF30\Form\Field as FOFFormField;

// Joomla! class inclusion
use JFactory, JHtml, JText, JFormHelper;

// Protect from unauthorized access
defined('FOF30_INCLUDED') or die;

/**
 * Form Field class for the F0F framework
 * Media selection field. This is an alias of the "media" field type.
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class Image extends Media
{
}
