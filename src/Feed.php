<?php

declare(strict_types=1);

namespace Ttt;

/** Renders the Atom feed and the XML sitemap. */
final class Feed
{
    /**
     * @param list<array{title: string, url: string, updated: int, summary: string, content: string}> $entries Newest first; URLs absolute.
     */
    public static function atom(string $title, string $subtitle, string $homeUrl, string $selfUrl, array $entries): string
    {
        $w = self::writer();
        $w->startElementNs(null, 'feed', 'http://www.w3.org/2005/Atom');
        self::text($w, 'title', $title);
        if ($subtitle !== '') {
            self::text($w, 'subtitle', $subtitle);
        }
        self::link($w, $selfUrl, 'self');
        self::link($w, $homeUrl, 'alternate');
        self::text($w, 'id', $homeUrl);
        self::text($w, 'updated', gmdate(DATE_ATOM, max(array_column($entries, 'updated') ?: [0])));
        foreach ($entries as $e) {
            $w->startElement('entry');
            self::text($w, 'title', $e['title']);
            self::link($w, $e['url'], 'alternate');
            self::text($w, 'id', $e['url']);
            self::text($w, 'updated', gmdate(DATE_ATOM, $e['updated']));
            if ($e['summary'] !== '') {
                $w->startElement('summary');
                $w->writeAttribute('type', 'html');
                $w->text(self::clean($e['summary']));
                $w->endElement();
            }
            $w->startElement('content');
            $w->writeAttribute('type', 'html');
            $w->text(self::clean($e['content']));
            $w->endElement();
            $w->endElement();
        }
        $w->endElement();
        return $w->outputMemory();
    }

    /**
     * @param list<array{url: string, updated: int}> $pages Absolute URLs with their last change.
     */
    public static function sitemap(array $pages): string
    {
        $w = self::writer();
        $w->startElementNs(null, 'urlset', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        foreach ($pages as $page) {
            $w->startElement('url');
            self::text($w, 'loc', $page['url']);
            self::text($w, 'lastmod', gmdate(DATE_ATOM, $page['updated']));
            $w->endElement();
        }
        $w->endElement();
        return $w->outputMemory();
    }

    private static function writer(): \XMLWriter
    {
        $w = new \XMLWriter();
        $w->openMemory();
        $w->setIndent(true);
        $w->startDocument('1.0', 'UTF-8');
        return $w;
    }

    private static function text(\XMLWriter $w, string $name, string $text): void
    {
        $w->writeElement($name, self::clean($text));
    }

    private static function link(\XMLWriter $w, string $href, string $rel): void
    {
        $w->startElement('link');
        $w->writeAttribute('rel', $rel);
        $w->writeAttribute('href', $href);
        $w->endElement();
    }

    /** Drop characters that are not allowed in XML 1.0 (a stray control character would break the whole feed). */
    private static function clean(string $text): string
    {
        return (string) preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text);
    }
}
