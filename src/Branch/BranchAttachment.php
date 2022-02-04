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

namespace Brain\Hierarchy\Branch;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class BranchAttachment implements Branch
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'attachment';
    }

    /**
     * @param \WP_Query $query
     * @return bool
     */
    public function is(\WP_Query $query): bool
    {
        return $query->is_attachment();
    }

    /**
     * @param \WP_Query $query
     * @return list<string>
     */
    public function leaves(\WP_Query $query): array
    {
        /** @var \WP_Post $post */
        $post = $query->get_queried_object();
        ($post instanceof \WP_Post) or $post = new \WP_Post((object)['ID' => 0]);

        $leaves = [];
        $mimetype = $post->post_mime_type ? explode('/', $post->post_mime_type, 2) : null;
        if ($mimetype && !empty($mimetype[0])) {
            $leaves = !empty($mimetype[1])
                ? [$mimetype[0], $mimetype[1], "{$mimetype[0]}_{$mimetype[1]}"]
                : [$mimetype[0]];
        }

        $leaves[] = 'attachment';

        return $leaves;
    }
}
