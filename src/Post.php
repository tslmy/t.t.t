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
        /** @var array<string, mixed> Front matter. */
        public readonly array $meta = [],
    ) {
    }

    /** Folder containing the post, relative to the content folder ('' for the root). */
    public function dir(): string
    {
        $dir = dirname($this->relPath);
        return $dir === '.' ? '' : $dir;
    }

    /** The front matter `title`, else the file name without its extension. */
    public function title(): string
    {
        $title = $this->meta['title'] ?? null;
        return is_scalar($title) && trim((string) $title) !== ''
            ? trim((string) $title)
            : pathinfo($this->relPath, PATHINFO_FILENAME);
    }

    /** The front matter `date`, else the file's modification time. */
    public function date(): int
    {
        return FrontMatter::timestamp($this->meta['date'] ?? null) ?? $this->mtime;
    }

    /** The front matter `summary` (Markdown), or '' if there is none. */
    public function summary(): string
    {
        $summary = $this->meta['summary'] ?? null;
        return is_scalar($summary) ? trim((string) $summary) : '';
    }

    public function isDraft(): bool
    {
        return filter_var($this->meta['draft'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }
}
