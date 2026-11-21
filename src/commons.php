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
define('DISQUS_SHORTNAME', env_string('TTT_DISQUS_SHORTNAME', ''));   // Empty disables comments.
define('ANALYTICS_DOMAIN', env_string('TTT_ANALYTICS_DOMAIN', ''));   // Empty disables analytics.
define('ANALYTICS_SCRIPT_URL', env_string('TTT_ANALYTICS_SCRIPT_URL', 'https://plausible.io/js/script.js'));
define('EXT_ALLOWED', ['txt', 'md']);

// Where the posts live: relative to the working directory (public/), and absolute.
define('CONTENT_REL_DIR', trim(constant('CONTENT_DIR'), '/'));
define('CONTENT_ABS_DIR', getcwd().'/'.CONTENT_REL_DIR);

use League\CommonMark\GithubFlavoredMarkdownConverter;
use Michelf\SmartyPants;

/**
 * Get the content of a file, convert Markdown to HTML, apply SmartyPants, and add internal links.
 *
 * @param string $file_name The path to the file to read.
 * @param int $max_size The maximum number of bytes to read from the file. Use 0 for no limit.
 * @return string The processed HTML content.
 */
function get_content(string $file_name, int $max_size): string
{
    $converter = new GithubFlavoredMarkdownConverter([
        'html_input' => 'strip',
        'allow_unsafe_links' => false,
    ]);
    $content = (string) file_get_contents($file_name);
    if ($max_size>0) {
        $content = truncate_text($content, $max_size);
    }
    // Internal links must be rewritten *before* conversion: GitHub-flavored Markdown
    // would otherwise swallow `~name~` as strikethrough. `~~double~~` is left alone.
    $content = preg_replace_callback(
        '/(?<!~)~([^~\s][^~\n]*?)~(?!~)/',
        function (array $m): string {
            $parts = explode(':', $m[1], 2);
            return internal_link($parts[0], $parts[1] ?? $parts[0]);
        },
        trim($content)
    );
    $content = $converter->convertToHtml($content);
    $content = SmartyPants::defaultTransform($content);
    return $content;
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
