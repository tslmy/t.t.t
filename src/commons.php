<?php

/**
 * Read a setting from the environment, falling back to a default.
 *
 * @param string $name The environment variable name.
 * @param string $default The value to use when the variable is unset or empty.
 * @return string The setting.
 */
function env_string(string $name, string $default): string
{
    $value = getenv($name);
    return ($value === false || $value === '') ? $default : $value;
}

/**
 * Like env_string(), but for positive integers; anything else yields the default.
 *
 * @param string $name The environment variable name.
 * @param int $default The value to use when the variable is unset or not a positive integer.
 * @return int The setting.
 */
function env_int(string $name, int $default): int
{
    $value = filter_var(getenv($name), FILTER_VALIDATE_INT);
    return ($value === false || $value < 1) ? $default : $value;
}

// Settings. Override any of them with the environment variable named in the comment.
define('SITE_NAME', env_string('TTT_SITE_NAME', 'My blog'));
define('SITE_DESC', env_string('TTT_SITE_DESC', 'Powered by t.t.t, a database-free blog engine.'));
define('ITEMS_DISPLAYED_PER_PAGE', env_int('TTT_ITEMS_PER_PAGE', 10));
define('PREVIEW_SIZE_IN_BYTES', env_int('TTT_PREVIEW_BYTES', 1000));  // Roughly how much of each post the index shows.
define('CONTENT_DIR', env_string('TTT_CONTENT_DIR', 'content'));      // Where the text files live, relative to the web root.
// Comments via giscus (GitHub Discussions). All four must be set to enable them; get them from https://giscus.app.
define('GISCUS_REPO', env_string('TTT_GISCUS_REPO', ''));                  // owner/name of the repository holding the discussions
define('GISCUS_REPO_ID', env_string('TTT_GISCUS_REPO_ID', ''));
define('GISCUS_CATEGORY', env_string('TTT_GISCUS_CATEGORY', ''));          // discussion category name, e.g. Announcements
define('GISCUS_CATEGORY_ID', env_string('TTT_GISCUS_CATEGORY_ID', ''));
define('ANALYTICS_DOMAIN', env_string('TTT_ANALYTICS_DOMAIN', ''));   // Empty disables analytics.
define('ANALYTICS_SCRIPT_URL', env_string('TTT_ANALYTICS_SCRIPT_URL', 'https://plausible.io/js/script.js'));
define('SHOW_DRAFTS', env_string('TTT_SHOW_DRAFTS', '') !== '');         // Set (to anything) to preview posts marked `draft: true`.
define('SITE_URL', env_string('TTT_SITE_URL', ''));                      // e.g. https://example.com — used in the feed and sitemap; guessed from the request if empty.
define('EXT_ALLOWED', ['txt', 'md']);

// Where the posts live: relative to the working directory (public/), and absolute.
define('CONTENT_REL_DIR', trim(constant('CONTENT_DIR'), '/'));
define('CONTENT_ABS_DIR', getcwd().'/'.CONTENT_REL_DIR);

use League\CommonMark\GithubFlavoredMarkdownConverter;
use Michelf\SmartyPants;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Get the content of a post file as HTML (its front matter header, if any, is left out).
 *
 * @param string $file_name The path to the file to read.
 * @param int $max_size The maximum number of bytes to read from the file. Use 0 for no limit.
 * @return string The processed HTML content.
 */
function get_content(string $file_name, int $max_size): string
{
    $content = split_front_matter((string) file_get_contents($file_name))[1];
    if ($max_size>0) {
        $content = truncate_text($content, $max_size);
    }
    return render_markdown($content);
}

/**
 * Convert Markdown to HTML, apply SmartyPants, and turn `~post~` / `~post:label~` into links.
 *
 * @param string $markdown The Markdown text.
 * @return string The HTML.
 */
function render_markdown(string $markdown): string
{
    $converter = new GithubFlavoredMarkdownConverter([
        'html_input' => 'strip',
        'allow_unsafe_links' => false,
    ]);
    // Internal links must be rewritten *before* conversion: GitHub-flavored Markdown
    // would otherwise swallow `~name~` as strikethrough. `~~double~~` is left alone.
    $markdown = preg_replace_callback(
        '/(?<!~)~([^~\s][^~\n]*?)~(?!~)/',
        function (array $m): string {
            $parts = explode(':', $m[1], 2);
            return internal_link($parts[0], $parts[1] ?? $parts[0]);
        },
        trim($markdown)
    );
    return SmartyPants::defaultTransform((string) $converter->convertToHtml($markdown));
}

/**
 * Split a post into its optional YAML header ("front matter") and its body:
 *
 *     ---
 *     title: Hello
 *     date: 2024-05-01 09:30
 *     summary: Shown on the index instead of the start of the post.
 *     draft: true
 *     ---
 *     Post text…
 *
 * A missing or malformed header yields no metadata and the text untouched, so a typo
 * can never stop a post from rendering.
 *
 * @param string $text The whole file.
 * @return array{0: array<string, mixed>, 1: string} [metadata, body]
 */
function split_front_matter(string $text): array
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

/**
 * Read a post file's front matter. Only files that start with a fence are read in full.
 *
 * @return array<string, mixed>
 */
function read_post_meta(string $path): array
{
    $handle = fopen($path, 'rb');
    if ($handle === false) {
        return [];
    }
    $start = (string) fread($handle, 6);
    fclose($handle);
    if (!str_contains($start, '---')) {
        return [];
    }
    return split_front_matter((string) file_get_contents($path))[0];
}

/** The front matter `title`, else the file name without its extension. */
function post_title(array $meta, string $path): string
{
    $title = $meta['title'] ?? null;
    return is_scalar($title) && trim((string) $title) !== ''
        ? trim((string) $title)
        : pathinfo($path, PATHINFO_FILENAME);
}

/** The front matter `date` (a YAML date, a Unix timestamp or a date string), else $mtime. */
function post_date(array $meta, int $mtime): int
{
    $value = $meta['date'] ?? null;
    if ($value instanceof DateTimeInterface) {
        return $value->getTimestamp();
    }
    if (is_int($value)) {
        return $value;
    }
    if (is_string($value) && ($t = strtotime($value)) !== false) {
        return $t;
    }
    return $mtime;
}

/** The front matter `summary` (Markdown), or '' if there is none. */
function post_summary(array $meta): string
{
    $summary = $meta['summary'] ?? null;
    return is_scalar($summary) ? trim((string) $summary) : '';
}

/** Whether the front matter marks the post as `draft: true`. */
function post_is_draft(array $meta): bool
{
    return filter_var($meta['draft'] ?? false, FILTER_VALIDATE_BOOLEAN);
}

/**
 * The settings needed to embed giscus comments, or null if comments aren't (fully) configured.
 *
 * @return array{repo: string, repo_id: string, category: string, category_id: string}|null
 */
function giscus_settings(string $repo, string $repo_id, string $category, string $category_id): ?array
{
    $repo = trim($repo);
    if (!preg_match('#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#', $repo)) {
        return null;
    }
    $repo_id = trim($repo_id);
    $category = trim($category);
    $category_id = trim($category_id);
    if ($repo_id === '' || $category === '' || $category_id === '') {
        return null;
    }
    return ['repo' => $repo, 'repo_id' => $repo_id, 'category' => $category, 'category_id' => $category_id];
}

/**
 * Build a Markdown link to another post. The target is URL-encoded.
 *
 * @param string $target The name of the post, as typed between the tildes.
 * @param string $label The text to show (Markdown).
 * @return string The Markdown link.
 */
function internal_link(string $target, string $label): string
{
    return '['.addcslashes($label, '[]\\').'](view.php?name='.rawurlencode($target).')';
}

/**
 * Shorten text to at most $max_bytes without splitting a multi-byte character,
 * preferring to cut at the last paragraph break.
 *
 * @param string $text The text to shorten.
 * @param int $max_bytes The maximum number of bytes to keep.
 * @return string The (possibly) shortened text.
 */
function truncate_text(string $text, int $max_bytes): string
{
    if (strlen($text) <= $max_bytes) {
        return $text;
    }
    $cut = mb_strcut($text, 0, $max_bytes, 'UTF-8');
    $para = strrpos($cut, "\n\n");
    return $para === false ? $cut : substr($cut, 0, $para);
}

/**
 * Resolve a user-supplied relative path against a base directory.
 *
 * @param string $base_dir Absolute path of the base directory (no trailing slash).
 * @param string $relative The user-supplied relative path.
 * @return string|null The real absolute path, or null if it doesn't exist or escapes the base directory.
 */
function resolve_inside(string $base_dir, string $relative): ?string
{
    $path = realpath($base_dir.'/'.$relative);
    if ($path === false) {
        return null;
    }
    if ($path !== $base_dir && !str_starts_with($path, $base_dir.'/')) {
        return null;
    }
    return $path;
}

/**
 * Stop the request with an HTTP status code and a short plain message.
 *
 * @param int $code The HTTP status code.
 * @param string $message The message to show.
 * @return never
 */
function abort_with(int $code, string $message): never
{
    http_response_code($code);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $message;
    exit;
}

/**
 * Print a breadcrumb navigation HTML string based on the given crumbs and current path.
 *
 * @param array $crumbs Array of folder names representing the breadcrumb trail.
 * @param string $cur_path The current absolute path.
 * @return string The HTML string representing the breadcrumb navigation.
 */
function print_breadcrumb(array $crumbs, string $cur_path): string
{
    $output = "<a href=\"index.php\">Home</a>";
    $absolute_path='';
    foreach ($crumbs as $i => $folder_name) {
        if ($folder_name=='') {
            continue;
        }
        $absolute_path = $absolute_path.'/'.$folder_name;
        if ($absolute_path==$cur_path) {
            $output .= ' &gt; '.htmlspecialchars($folder_name);
        } else {
            $output .= ' &gt; <a href="index.php?folder='.urlencode($absolute_path).'">'.htmlspecialchars($folder_name).'</a>';
        }
    }
    return $output;
}

/**
 * The site's origin ("https://example.com", no path): the configured one if valid, else guessed from the request.
 *
 * @param array<string, mixed> $server The $_SERVER array.
 * @param string $configured The TTT_SITE_URL setting.
 */
function site_origin(array $server, string $configured = ''): string
{
    $parts = $configured === '' ? false : parse_url($configured);
    if (is_array($parts) && in_array($parts['scheme'] ?? '', ['http', 'https'], true) && ($parts['host'] ?? '') !== '') {
        return $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
    }
    $https = isset($server['HTTPS']) && $server['HTTPS'] !== '' && $server['HTTPS'] !== 'off';
    $host = is_string($server['HTTP_HOST'] ?? null) && preg_match('/^[A-Za-z0-9.\-]+(:\d+)?$/', $server['HTTP_HOST'])
        ? $server['HTTP_HOST']
        : 'localhost';
    return ($https ? 'https' : 'http').'://'.$host;
}

/**
 * The URL of a page of this site, absolute, so it works in a feed. Handles installs in a sub-directory.
 *
 * @param array<string, mixed> $server The $_SERVER array.
 * @param string $relative Path relative to the site root, e.g. 'view.php?name=a.md'. '' is the home page.
 */
function absolute_url(array $server, string $relative = ''): string
{
    $script = is_string($server['SCRIPT_NAME'] ?? null) ? $server['SCRIPT_NAME'] : '/index.php';
    $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
    return site_origin($server, constant('SITE_URL')).$base.'/'.ltrim($relative, '/');
}

/**
 * Every post file under a directory, newest first.
 *
 * @param string $abs_dir Absolute path of the content folder.
 * @param list<string> $extensions Allowed file extensions.
 * @return list<array{path: string, rel: string, mtime: int}>
 */
function list_post_files(string $abs_dir, array $extensions): array
{
    $posts = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($abs_dir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array($file->getExtension(), $extensions, true)) {
            $path = $file->getPathname();
            $posts[] = ['path' => $path, 'rel' => ltrim(substr($path, strlen($abs_dir)), '/'), 'mtime' => (int) $file->getMTime()];
        }
    }
    usort($posts, fn (array $a, array $b) => [$b['mtime'], $a['rel']] <=> [$a['mtime'], $b['rel']]);
    return $posts;
}

/**
 * The posts that should be public (drafts excluded unless $show_drafts), newest first by their
 * front matter date, with their title and summary. Used by the feed and the sitemap.
 *
 * @param string $abs_dir Absolute path of the content folder.
 * @param list<string> $extensions Allowed file extensions.
 * @return list<array{path: string, rel: string, title: string, date: int, summary: string}>
 */
function published_posts(string $abs_dir, array $extensions, bool $show_drafts = false): array
{
    $posts = [];
    foreach (list_post_files($abs_dir, $extensions) as $file) {
        $meta = read_post_meta($file['path']);
        if (post_is_draft($meta) && !$show_drafts) {
            continue;
        }
        $posts[] = [
            'path' => $file['path'],
            'rel' => $file['rel'],
            'title' => post_title($meta, $file['rel']),
            'date' => post_date($meta, $file['mtime']),
            'summary' => post_summary($meta),
        ];
    }
    usort($posts, fn (array $a, array $b) => [$b['date'], $a['rel']] <=> [$a['date'], $b['rel']]);
    return $posts;
}

/** Plain-text teaser of some HTML, for <meta name="description">. */
function html_excerpt(string $html, int $width = 200): string
{
    $text = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5)));
    return mb_strimwidth($text, 0, $width, '…');
}

/** Drop characters that are not allowed in XML 1.0 (a stray control character would break a whole feed). */
function xml_clean(string $text): string
{
    return (string) preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text);
}

/**
 * An Atom feed.
 *
 * @param list<array{title: string, url: string, updated: int, content: string, summary?: string}> $entries Newest first; URLs absolute. `summary` is HTML.
 */
function atom_feed(string $title, string $subtitle, string $home_url, string $self_url, array $entries): string
{
    $w = new XMLWriter();
    $w->openMemory();
    $w->setIndent(true);
    $w->startDocument('1.0', 'UTF-8');
    $w->startElementNs(null, 'feed', 'http://www.w3.org/2005/Atom');
    $w->writeElement('title', xml_clean($title));
    if ($subtitle !== '') {
        $w->writeElement('subtitle', xml_clean($subtitle));
    }
    foreach ([[$self_url, 'self'], [$home_url, 'alternate']] as [$href, $rel]) {
        $w->startElement('link');
        $w->writeAttribute('rel', $rel);
        $w->writeAttribute('href', $href);
        $w->endElement();
    }
    $w->writeElement('id', $home_url);
    $w->writeElement('updated', gmdate(DATE_ATOM, max(array_column($entries, 'updated') ?: [0])));
    foreach ($entries as $e) {
        $w->startElement('entry');
        $w->writeElement('title', xml_clean($e['title']));
        $w->startElement('link');
        $w->writeAttribute('rel', 'alternate');
        $w->writeAttribute('href', $e['url']);
        $w->endElement();
        $w->writeElement('id', $e['url']);
        $w->writeElement('updated', gmdate(DATE_ATOM, $e['updated']));
        if (($e['summary'] ?? '') !== '') {
            $w->startElement('summary');
            $w->writeAttribute('type', 'html');
            $w->text(xml_clean($e['summary']));
            $w->endElement();
        }
        $w->startElement('content');
        $w->writeAttribute('type', 'html');
        $w->text(xml_clean($e['content']));
        $w->endElement();
        $w->endElement();
    }
    $w->endElement();
    return $w->outputMemory();
}

/**
 * An XML sitemap.
 *
 * @param list<array{url: string, updated: int}> $pages Absolute URLs with their last change.
 */
function sitemap_xml(array $pages): string
{
    $w = new XMLWriter();
    $w->openMemory();
    $w->setIndent(true);
    $w->startDocument('1.0', 'UTF-8');
    $w->startElementNs(null, 'urlset', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    foreach ($pages as $page) {
        $w->startElement('url');
        $w->writeElement('loc', $page['url']);
        $w->writeElement('lastmod', gmdate(DATE_ATOM, $page['updated']));
        $w->endElement();
    }
    $w->endElement();
    return $w->outputMemory();
}
