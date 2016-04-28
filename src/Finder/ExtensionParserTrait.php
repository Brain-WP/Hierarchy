<?php
/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy\Finder;

trait ExtensionParserTrait {

	/**
	 * Pattern used to trim the individual extensions.
	 * Default trims dots, spaces, tabs and new lines.
	 *
	 * @var string
	 */
	private $_trimPattern = ". \t\n\r\0\x0B";

	/**
	 * Parse a string or an array of strings into an array of extensions.
	 *
	 * @param string|string[] $extensions
	 * @return string[]
	 */
	private function parseExtensions($extensions)
	{
		$parsed = [];
		$extensions = is_string($extensions) ? explode('|', $extensions) : (array) $extensions;
		foreach ($extensions as $extension) {
			if (is_string($extension)) {
				$extension = strtolower(trim($extension, $this->_trimPattern));
				in_array($extension, $parsed, true) or $parsed[] = $extension;
			}
		}

		return $parsed;
	}
}
