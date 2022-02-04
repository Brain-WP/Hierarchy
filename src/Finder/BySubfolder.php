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
 * Very similar to the way WordPress core works, however, it allows to search
 * templates in a subfolder (for both parent and child themes) and to use one or
 * more custom file extensions (defaults to php).
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class BySubfolder implements TemplateFinder
{
    /**
     * @var ByFolders
     */
    private $finder;

    /**
     * @param string $subfolder
     * @param string $extension
     * @param string ...$extensions
     */
    public function __construct(string $subfolder, string $extension = 'php', string ...$extensions)
    {
        $stylesheet = trailingslashit(get_stylesheet_directory()) . $subfolder;
        $template = trailingslashit(get_template_directory()) . $subfolder;
        $folders = [$stylesheet];
        ($stylesheet !== $template) and $folders[] = $template;
        $this->finder = new ByFolders($folders, $extension, ...$extensions);
    }

    /**
     * @param string $template
     * @param string $type
     * @return string
     */
    public function find(string $template, string $type): string
    {
        return $this->finder->find($template, $type);
    }

    /**
     * @param array $templates
     * @param string $type
     * @return string
     */
    public function findFirst(array $templates, string $type): string
    {
        return $this->finder->findFirst($templates, $type);
    }
}
