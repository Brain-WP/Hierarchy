<?php
/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy\Tests\Unit\Finder;

use Brain\Hierarchy\Finder\ExtensionParserTrait;
use Brain\Hierarchy\Tests\TestCase;


/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Hierarchy
 */
class FileExtensionPredicateTest extends TestCase
{
	use ExtensionParserTrait;

	/**
	 * @dataProvider extensionDataProvider
	 */
	public function testExtensionParser($input, $output)
	{
		assertEquals($output, $extension = $this->parseExtensions($input));
	}

	public function extensionDataProvider() {
		return [
			// $input, $output
		    [ 'php', [ 'php' ] ],
			[ "\0\n\t .PhP \0\n\t", [ 'php' ] ],
		    [ 'twig|php|html', [ 'twig', 'php', 'html' ] ],
			[ "\nTWIG | php\t | .Html", [ 'twig', 'php', 'html' ] ],
		];
	}
}
