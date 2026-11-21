<?php

declare(strict_types=1);

namespace Ttt;

/** A post: one Markdown/text file under the content folder. */
final class Post
{
    public function __construct(
        public readonly string $path,     // absolute path on disk
        public readonly string $relPath,  // relative to the content folder, e.g. 'essay/title.md'
        public readonly int $mtime,
    ) {
    }

    /** Folder containing the post, relative to the content folder ('' for the root). */
    public function dir(): string
    {
        $dir = dirname($this->relPath);
        return $dir === '.' ? '' : $dir;
    }

    public function title(): string
    {
        return pathinfo($this->relPath, PATHINFO_FILENAME);
    }

    public function date(): int
    {
        return $this->mtime;
    }
}
