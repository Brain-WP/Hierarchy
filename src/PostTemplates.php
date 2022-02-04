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

namespace Brain\Hierarchy;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class PostTemplates
{
    /**
     * @var array<string, list<string>>
     */
    private $templates = [];

    /**
     * @param \WP_Post $post
     * @return string
     */
    public function findFor(\WP_Post $post): string
    {
        if (!$post->ID || !$post->post_type) {
            return '';
        }

        $stored = get_page_template_slug($post);
        if (!$stored || validate_file($stored) !== 0) {
            return '';
        }

        $stored = wp_normalize_path($stored);
        $templates = $this->templatesForType($post->post_type);

        foreach ($templates as $template) {
            if ($template === $stored) {
                $dir = dirname($template);
                $filename = pathinfo($template, PATHINFO_FILENAME);

                return $dir === '.' ? $filename : "{$dir}/{$filename}";
            }
        }

        return '';
    }

    /**
     * @param string $postType
     * @return list<string>
     */
    private function templatesForType(string $postType): array
    {
        if (array_key_exists($postType, $this->templates)) {
            return $this->templates[$postType];
        }

        $this->templates[$postType] = [];
        $templates = array_keys((array)wp_get_theme()->get_page_templates(null, $postType));
        foreach ($templates as $template) {
            if ($template && is_string($template)) {
                $this->templates[$postType][] = wp_normalize_path(sanitize_file_name($template));
            }
        }

        return $this->templates[$postType];
    }
}
