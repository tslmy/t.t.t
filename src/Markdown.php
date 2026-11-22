<?php

declare(strict_types=1);

namespace Ttt;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use Michelf\SmartyPants;

/** Turns post text into HTML. */
final class Markdown
{
    private GithubFlavoredMarkdownConverter $converter;

    public function __construct(private Urls $urls)
    {
        $this->converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * Render a post file as HTML.
     *
     * @param int $maxBytes Only use roughly this many bytes of the file; 0 or less means all of it.
     */
    public function renderFile(string $path, int $maxBytes = 0): string
    {
        $body = FrontMatter::split((string) file_get_contents($path))[1];
        return $this->render($maxBytes > 0 ? self::truncate($body, $maxBytes) : $body);
    }

    /** Convert Markdown to HTML, apply SmartyPants, and resolve `~post~` / `~post:label~` links. */
    public function render(string $text): string
    {
        // Internal links must be rewritten *before* conversion: GitHub-flavored Markdown
        // would otherwise swallow `~name~` as strikethrough. `~~double~~` is left alone.
        $text = preg_replace_callback(
            '/(?<!~)~([^~\s][^~\n]*?)~(?!~)/',
            function (array $m): string {
                $parts = explode(':', $m[1], 2);
                return $this->internalLink($parts[0], $parts[1] ?? $parts[0]);
            },
            trim($text)
        ) ?? '';
        return SmartyPants::defaultTransform($this->converter->convertToHtml($text)->getContent());
    }

    /** A Markdown link to another post; the target is URL-encoded. */
    public function internalLink(string $target, string $label): string
    {
        return '['.addcslashes($label, '[]\\').']('.$this->urls->post($target).')';
    }

    /**
     * Shorten text to at most $maxBytes without splitting a multi-byte character,
     * preferring to cut at the last paragraph break.
     */
    public static function truncate(string $text, int $maxBytes): string
    {
        if (strlen($text) <= $maxBytes) {
            return $text;
        }
        $cut = mb_strcut($text, 0, $maxBytes, 'UTF-8');
        $para = strrpos($cut, "\n\n");
        return $para === false ? $cut : substr($cut, 0, $para);
    }
}
