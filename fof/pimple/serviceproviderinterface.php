<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  model
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace FOF30\Pimple;

// Protect from unauthorized access
defined('FOF30_INCLUDED') or die;

/**
 * Pimple service provider interface.
 *
 * @author Fabien Potencier
 * @author Dominik Zogg
 *
 * @codeCoverageIgnore
 */
interface ServiceProviderInterface
{
	/**
	 * Registers services on the given container.
	 *
	 * This method should only be used to configure services and parameters.
	 * It should not get services.
	 *
	 * @param Pimple $pimple An Container instance
	 */
	public function register(Pimple $pimple);
}