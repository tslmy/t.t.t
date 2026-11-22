<?php

declare(strict_types=1);

namespace Ttt;

/** Optional comment section, powered by giscus (https://giscus.app): comments are GitHub Discussions. */
final class Comments
{
    /**
     * The settings the page needs to embed giscus, or null if comments aren't (fully) configured.
     *
     * @return array{repo: string, repo_id: string, category: string, category_id: string}|null
     */
    public static function giscus(string $repo, string $repoId, string $category, string $categoryId): ?array
    {
        $repo = trim($repo);
        if (!preg_match('#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#', $repo)) {
            return null;
        }
        $repoId = trim($repoId);
        $category = trim($category);
        $categoryId = trim($categoryId);
        if ($repoId === '' || $category === '' || $categoryId === '') {
            return null;
        }
        return ['repo' => $repo, 'repo_id' => $repoId, 'category' => $category, 'category_id' => $categoryId];
    }
}
