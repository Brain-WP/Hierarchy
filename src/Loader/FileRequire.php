<?php

/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Brain\Hierarchy\Loader;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class FileRequire implements Loader
{
    /**
     * @param string $templatePath
     * @return string
     */
    public function load(string $templatePath): string
    {
        if ((!defined('WP_DEBUG') || !WP_DEBUG) && !file_exists($templatePath)) {
            return '';
        }

        ob_start();
        require $templatePath;

        return trim(ob_get_clean() ?: '');
    }
}
