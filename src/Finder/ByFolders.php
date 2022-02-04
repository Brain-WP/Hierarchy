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

use Brain\Hierarchy\FileExtensionPredicate;

/**
 * Very similar to the way WordPress core works, however, it allows to search
 * templates within arbitrary folders and to use one or more custom file
 * extensions. By default, it looks through stylesheet and template folders and
 * allows file extension to be php, so it acts exactly like core.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class ByFolders implements TemplateFinder
{
    use FindFirstTrait;

    /**
     * @var list<string>
     */
    private $folders;

    /**
     * @var list<string>
     */
    private $extensions;

    /**
     * @param array $folders
     * @param string $extension
     * @param string ...$extensions
     */
    public function __construct(
        array $folders = [],
        string $extension = 'php',
        string ...$extensions
    ) {

        $parsedFolders = [];
        foreach ($folders as $folder) {
            if ($folder && is_string($folder)) {
                $normalized = trailingslashit(wp_normalize_path($folder));
                $parsedFolders[] = $normalized;
            }
        }
        if (!$parsedFolders) {
            $parsedFolders[] = trailingslashit(wp_normalize_path(get_stylesheet_directory()));
            $parsedFolders[] = trailingslashit(wp_normalize_path(get_template_directory()));
        }

        $this->folders = array_values(array_unique($parsedFolders));
        $this->extensions = FileExtensionPredicate::parseExtensions($extension, ...$extensions);
    }

    /**
     * @param string $template
     * @param string $type
     * @return string
     */
    public function find(string $template, string $type): string
    {
        foreach ($this->folders as $folder) {
            $found = $this->findInFolder($folder, $template);
            if ($found) {
                return $found;
            }
        }

        return '';
    }

    /**
     * @param string $folder
     * @param string $template
     * @return string|null
     */
    private function findInFolder(string $folder, string $template): ?string
    {
        foreach ($this->extensions as $extension) {
            $path = $folder . $template;
            $extension and $path = "{$path}.{$extension}";
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
