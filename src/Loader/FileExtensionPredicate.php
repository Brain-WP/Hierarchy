<?php
/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy\Loader;

use Brain\Hierarchy\Finder\ExtensionParserTrait;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Hierarchy
 */
class FileExtensionPredicate
{
    use ExtensionParserTrait;

    /**
     * @var string[]
     */
    private $extension = [];

    /**
     * @param string|string[] $extension
     */
    public function __construct($extension)
    {
        $this->extension = $this->parseExtensions($extension);
    }

    /**
     * @param  string $templatePath
     * @return bool
     */
    public function __invoke($templatePath)
    {
        $ext = strtolower(pathinfo($templatePath, PATHINFO_EXTENSION));

        return in_array($ext, $this->extension, true);
    }
}
