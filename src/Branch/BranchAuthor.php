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
final class BranchAuthor implements Branch
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'author';
    }

    /**
     * @param \WP_Query $query
     * @return bool
     */
    public function is(\WP_Query $query): bool
    {
        return $query->is_author();
    }

    /**
     * @param \WP_Query $query
     * @return list<string>
     */
    public function leaves(\WP_Query $query): array
    {
        /** @var \WP_User $user */
        $user = $query->get_queried_object();

        if (!$user instanceof \WP_User) {
            return ['author'];
        }

        return [
            "author-{$user->user_nicename}",
            "author-{$user->ID}",
            'author',
        ];
    }
}
