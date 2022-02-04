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
final class BranchEmbed implements Branch
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'embed';
    }

    /**
     * @param \WP_Query $query
     * @return bool
     */
    public function is(\WP_Query $query): bool
    {
        return $query->is_embed();
    }

    /**
     * @param \WP_Query $query
     * @return list<string>
     */
    public function leaves(\WP_Query $query): array
    {
        $post = $query->get_queried_object();
        $leaves = [];

        if ($post instanceof \WP_Post) {
            $postFormat = get_post_format($post);
            $postFormat and $leaves[] = "embed-{$post->post_type}-{$postFormat}";
            $leaves[] = "embed-{$post->post_type}";
        }

        $leaves[] = 'embed';

        return $leaves;
    }
}
