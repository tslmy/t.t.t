<?php

declare(strict_types=1);

namespace Ttt;

/**
 * Builds and parses the site's public URLs. All URLs are absolute paths under $base,
 * so the blog also works when installed in a sub-directory (e.g. /blog).
 */
final class Urls
{
    /**
     * @param string $base URL path prefix with no trailing slash ('' for the web root).
     * @param list<string> $extensions Post file extensions, stripped from post URLs.
     */
    public function __construct(private string $base = '', private array $extensions = ['md', 'txt'])
    {
    }

    /**
     * Derive the base path from the entry script's location (e.g. /blog/index.php => /blog).
     *
     * @param array<string, mixed> $server The $_SERVER array.
     * @param list<string> $extensions Post file extensions.
     */
    public static function fromServer(array $server, array $extensions = ['md', 'txt']): self
    {
        $script = is_string($server['SCRIPT_NAME'] ?? null) ? $server['SCRIPT_NAME'] : '/index.php';
        $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
        return new self($dir, $extensions);
    }

    /**
     * The site's origin ("https://example.com", no path): the configured one if valid, else guessed from the request.
     *
     * @param array<string, mixed> $server The $_SERVER array.
     */
    public static function origin(array $server, string $configured = ''): string
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

    public function base(): string
    {
        return $this->base;
    }

    public function home(): string
    {
        return $this->base.'/';
    }

    public function feed(): string
    {
        return $this->base.'/feed.xml';
    }

    /** URL of a static file under the web root, e.g. 'vendor/mvp.css'. */
    public function asset(string $path): string
    {
        return $this->base.'/'.$this->encode(ltrim($path, '/'));
    }

    /** URL of a post, given its path relative to the content folder ('essay/title.md' => /post/essay/title). */
    public function post(string $relPath): string
    {
        return $this->base.'/post/'.$this->encode($this->stripExtension(ltrim($relPath, '/')));
    }

    /** URL of a folder listing, given its path relative to the content folder ('' is the root). */
    public function folder(string $relDir): string
    {
        $relDir = trim($relDir, '/');
        return $relDir === '' ? $this->home() : $this->base.'/folder/'.$this->encode($relDir);
    }

    /** URL of the Nth page of a folder listing. */
    public function page(string $relDir, int $page): string
    {
        return $page <= 1 ? $this->folder($relDir) : $this->folder($relDir).'?page='.$page;
    }

    /**
     * Turn a request URI into a route path: base removed, decoded, no surrounding slashes.
     * Null if the URI isn't under the base path at all.
     */
    public function route(string $requestUri): ?string
    {
        $path = (string) parse_url($requestUri, PHP_URL_PATH);
        if ($this->base !== '') {
            if ($path === $this->base) {
                $path = '';
            } elseif (str_starts_with($path, $this->base.'/')) {
                $path = substr($path, strlen($this->base));
            } else {
                return null;
            }
        }
        return trim(rawurldecode($path), '/');
    }

    private function stripExtension(string $relPath): string
    {
        $ext = pathinfo($relPath, PATHINFO_EXTENSION);
        if ($ext !== '' && in_array(strtolower($ext), $this->extensions, true)) {
            return substr($relPath, 0, -(strlen($ext) + 1));
        }
        return $relPath;
    }

    private function encode(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}
