<?php

declare(strict_types=1);

namespace Ttt;

/** Finds posts and folders on disk, never leaving the content folder. */
final class PostRepository
{
    private string $root;

    /**
     * @param string $root Absolute path of the content folder.
     * @param list<string> $extensions Allowed post file extensions.
     * @param bool $includeDrafts Whether posts marked `draft: true` are visible.
     */
    public function __construct(string $root, private array $extensions, private bool $includeDrafts = false)
    {
        $this->root = rtrim($root, '/');
    }

    /**
     * Resolve a user-supplied relative path against a base directory.
     *
     * @return string|null The real absolute path, or null if it doesn't exist or escapes the base directory.
     */
    public static function resolveInside(string $baseDir, string $relative): ?string
    {
        if (str_contains($relative, "\0")) {
            return null;
        }
        $path = realpath($baseDir.'/'.$relative);
        if ($path === false) {
            return null;
        }
        if ($path !== $baseDir && !str_starts_with($path, $baseDir.'/')) {
            return null;
        }
        return $path;
    }

    /** The folder's path relative to the content folder ('' for the root), or null if there is no such folder. */
    public function folder(string $rel): ?string
    {
        $path = self::resolveInside($this->root, $rel);
        if ($path === null || !is_dir($path)) {
            return null;
        }
        return trim(substr($path, strlen($this->root)), '/');
    }

    /** Find a post by its extension-less path ('essay/title'). */
    public function find(string $slug): ?Post
    {
        foreach ($this->extensions as $ext) {
            $path = self::resolveInside($this->root, $slug.'.'.$ext);
            if ($path !== null && is_file($path)) {
                $post = $this->post($path);
                return $this->visible($post) ? $post : null;
            }
        }
        return null;
    }

    /**
     * All posts in a folder and its sub-folders, newest first.
     *
     * @return list<Post>
     */
    public function all(string $relFolder = ''): array
    {
        $dir = self::resolveInside($this->root, $relFolder);
        if ($dir === null || !is_dir($dir)) {
            return [];
        }
        $posts = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), $this->extensions, true)) {
                $post = $this->post($file->getPathname());
                if ($this->visible($post)) {
                    $posts[] = $post;
                }
            }
        }
        usort($posts, fn (Post $a, Post $b) => [$b->date(), $a->relPath] <=> [$a->date(), $b->relPath]);
        return $posts;
    }

    private function visible(Post $post): bool
    {
        return $this->includeDrafts || !$post->isDraft();
    }

    private function post(string $absPath): Post
    {
        return new Post(
            $absPath,
            ltrim(substr($absPath, strlen($this->root)), '/'),
            (int) filemtime($absPath),
            $this->readMeta($absPath),
        );
    }

    /** @return array<string, mixed> */
    private function readMeta(string $absPath): array
    {
        // Only files that start with a front matter fence are worth reading in full.
        $handle = fopen($absPath, 'rb');
        if ($handle === false) {
            return [];
        }
        $start = (string) fread($handle, 6);
        fclose($handle);
        if (!str_contains($start, '---')) {
            return [];
        }
        return FrontMatter::split((string) file_get_contents($absPath))[0];
    }
}
