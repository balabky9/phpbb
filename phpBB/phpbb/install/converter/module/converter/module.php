<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\install\converter\module\converter;

/**
 * Converter module for getting database parameters.
 */
class module extends \phpbb\install\module_base
{
	/**
	 * {@inheritdoc}
	 */
	public function get_navigation_stage_path()
	{
		return array('converter', 0, 'home');
	}
}