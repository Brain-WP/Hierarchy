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
 * Search templates looking for "localized" folders.
 *
 * Assuming
 *  - `$subfolder` is "templates"
 *  - `$extension` is "php"
 *  - current locale is "it_IT"
 *  - template to search is "page"
 *  - there is a child theme active
 *
 * It returns the first found among:
 *
 * 1. /path/to/child/theme/templates/it_IT/page.php
 * 2. /path/to/parent/theme/templates/it_IT/page.php
 * 3. /path/to/child/theme/templates/page.php
 * 4. /path/to/parent/theme/templates/page.php
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class Localized implements TemplateFinder
{
    use FindFirstTrait;

    /**
     * @var array
     */
    private $folders = [];

    /**
     * @var TemplateFinder
     */
    private $finder;

    /**
     * @param TemplateFinder|null $finder
     */
    public function __construct(?TemplateFinder $finder = null)
    {
        $this->finder = $finder ?: new ByFolders();
        $locale = get_locale();
        if (!$locale || !is_string($locale)) {
            return;
        }
        $sanitized = sanitize_file_name($locale);
        $this->folders = [$sanitized];
        if (strpos($locale, '_') !== false) {
            $part = explode('_', $locale, 2)[0];
            $part and $this->folders[] = $part;
        }
    }

    /**
     * @param string $template
     * @param string $type
     * @return string
     */
    public function find(string $template, string $type): string
    {
        $templates = [];
        foreach ($this->folders as $folder) {
            $templates[] = "{$folder}/{$template}";
        }
        $templates[] = $template;

        return $this->finder->findFirst($templates, $type);
    }
}
