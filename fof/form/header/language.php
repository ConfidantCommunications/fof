<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace FOF30\Form\Header;

use FOF30\Form\Header\Fieldselectable as FOFFormHeaderFieldselectable;
use FOF30\Form\Header as FOFFormHeader;

// Joomla! class inclusion
use JText, JHtml, JFactory, JLanguageHelper;

// Protect from unauthorized access
defined('FOF30_INCLUDED') or die;

/**
 * Language field header
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class Language extends FOFFormHeaderFieldselectable
{
	/**
	 * Method to get the filter options.
	 *
	 * @return  array  The filter option objects.
	 *
	 * @since   2.0
	 */
	protected function getOptions()
	{
		// Initialize some field attributes.
		$client = (string) $this->element['client'];

		if ($client != 'site' && $client != 'administrator')
		{
			$client = 'site';
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(
			parent::getOptions(), JLanguageHelper::createLanguageList($this->value, constant('JPATH_' . strtoupper($client)), true, true)
		);

		return $options;
	}
}
