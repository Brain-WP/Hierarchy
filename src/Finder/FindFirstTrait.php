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

namespace Brain\Hierarchy\Finder;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @method string find(string $template, string $type)
 */
trait FindFirstTrait
{
    /**
     * @param array $templates
     * @param string $type
     * @return string
     *
     * @see TemplateFinder::findFirst()
     */
    public function findFirst(array $templates, string $type): string
    {
        foreach ($templates as $template) {
            $found = is_string($template) ? $this->find($template, $type) : null;
            if ($found) {
                return $found;
            }
        }

        return '';
    }
}
