<?php

declare(strict_types=1);

namespace Ttt;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Optional YAML header at the top of a post:
 *
 *     ---
 *     title: Hello
 *     date: 2024-05-01 09:30
 *     summary: Shown on the index instead of the opening of the post.
 *     draft: true
 *     ---
 *     Post text…
 */
final class FrontMatter
{
    /**
     * Split a post into its metadata and its body. A missing or malformed header yields no
     * metadata and the text untouched, so a post can never fail to render because of its header.
     *
     * @return array{0: array<string, mixed>, 1: string} [metadata, body]
     */
    public static function split(string $text): array
    {
        $text = ltrim($text, "\u{FEFF}");  // byte order mark
        if (!preg_match('/\A---[ \t]*\R(.*?)\R---[ \t]*(?:\R|\z)/s', $text, $m)) {
            return [[], $text];
        }
        try {
            $meta = Yaml::parse($m[1], Yaml::PARSE_DATETIME);
        } catch (ParseException) {
            return [[], $text];
        }
        if (!is_array($meta)) {
            return [[], $text];
        }
        return [$meta, substr($text, strlen($m[0]))];
    }

    /** Read a header's `date` value (a YAML date, a timestamp, or a date string) as a Unix timestamp. */
    public static function timestamp(mixed $value): ?int
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && ($t = strtotime($value)) !== false) {
            return $t;
        }
        return null;
    }
}
