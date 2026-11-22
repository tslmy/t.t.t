<?php

declare(strict_types=1);

namespace Ttt;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * The whole site: maps a request to a Response.
 *
 *   /                      newest posts
 *   /folder/<path>         newest posts under a folder
 *   /post/<path>           one post (file extension omitted)
 *   /feed.xml              Atom feed of the newest posts
 *   /sitemap.xml           every post, for search engines
 *   view.php?name=, index.php?folder=   old URLs, redirected permanently
 */
final class App
{
    /**
     * @param array{site_name: string, site_desc: string, per_page: int, preview_bytes: int, content_dir: string, origin: string, feed_items: int, comments: array{repo: string, repo_id: string, category: string, category_id: string}|null, analytics_domain: string, analytics_script_url: string} $settings
     */
    public function __construct(
        private PostRepository $posts,
        private Urls $urls,
        private Markdown $markdown,
        private Environment $twig,
        private array $settings,
    ) {
    }

    /** Build the app from the TTT_* configuration and the current request's environment. */
    public static function create(array $server): self
    {
        $urls = Urls::fromServer($server, constant('EXT_ALLOWED'));
        return new self(
            new PostRepository(constant('CONTENT_ABS_DIR'), constant('EXT_ALLOWED')),
            $urls,
            new Markdown($urls),
            new Environment(new FilesystemLoader(dirname(__DIR__).'/templates')),
            [
                'site_name' => constant('SITE_NAME'),
                'site_desc' => constant('SITE_DESC'),
                'per_page' => constant('ITEMS_DISPLAYED_PER_PAGE'),
                'preview_bytes' => constant('PREVIEW_SIZE_IN_BYTES'),
                'content_dir' => constant('CONTENT_REL_DIR'),
                'origin' => Urls::origin($server, constant('SITE_URL')),
                'feed_items' => 20,
                'comments' => Comments::giscus(
                    constant('GISCUS_REPO'),
                    constant('GISCUS_REPO_ID'),
                    constant('GISCUS_CATEGORY'),
                    constant('GISCUS_CATEGORY_ID')
                ),
                'analytics_domain' => constant('ANALYTICS_DOMAIN'),
                'analytics_script_url' => constant('ANALYTICS_SCRIPT_URL'),
            ]
        );
    }

    /**
     * @param string $requestUri The request URI, including any query string.
     * @param array<string, mixed> $query The parsed query string ($_GET).
     */
    public function handle(string $requestUri, array $query = []): Response
    {
        $route = $this->urls->route($requestUri);
        if ($route === null) {
            return $this->error(404, 'Not found.');
        }

        if ($route === 'view.php') {
            $name = $query['name'] ?? null;
            return is_string($name) && $name !== ''
                ? Response::redirect($this->urls->post($name))
                : $this->error(400, 'Missing post name.');
        }
        if ($route === '' || $route === 'index.php') {
            $folder = $query['folder'] ?? null;
            if (is_string($folder) && trim($folder, '/') !== '') {
                return Response::redirect($this->urls->folder($folder));
            }
            return $this->index('', $query);
        }
        if ($route === 'feed.xml') {
            return $this->feed();
        }
        if ($route === 'sitemap.xml') {
            return $this->sitemap();
        }
        if (str_starts_with($route, 'folder/')) {
            return $this->index(substr($route, strlen('folder/')), $query);
        }
        if (str_starts_with($route, 'post/')) {
            return $this->post(substr($route, strlen('post/')));
        }
        return $this->error(404, 'Not found.');
    }

    /** @param array<string, mixed> $query */
    private function index(string $relFolder, array $query): Response
    {
        $folder = $this->posts->folder($relFolder);
        if ($folder === null) {
            return $this->error(404, 'Not found.');
        }

        $pager = new Pagerfanta(new ArrayAdapter($this->posts->all($folder)));
        $pager->setMaxPerPage($this->settings['per_page']);
        $requested = isset($query['page']) && is_string($query['page']) ? (int) $query['page'] : 1;
        $page = max(1, min($requested, $pager->getNbPages()));
        $pager->setCurrentPage($page);

        $entries = [];
        foreach ($pager->getCurrentPageResults() as $post) {
            $entries[] = [
                'title' => $post->title(),
                'url' => $this->urls->post($post->relPath),
                'date_str' => date('Y M d (D) H:i', $post->date()),
                'preview' => $this->markdown->renderFile($post->path, $this->settings['preview_bytes']),
                'breadcrumb' => $post->dir() === '' ? '' : Breadcrumb::render(explode('/', $post->dir()), '', $this->urls),
            ];
        }

        $pages = [];
        for ($i = 1; $i <= $pager->getNbPages(); $i++) {
            $pages[] = ['n' => $i, 'url' => $this->urls->page($folder, $i), 'current' => $i === $page];
        }

        $title = $this->settings['site_name'];
        if ($folder !== '') {
            $title .= ' - '.str_replace('/', ' > ', $folder);
        }

        return new Response(200, $this->twig->render('index.html.twig', $this->context([
            'head_title' => $title,
            'nav_breadcrumb' => $folder === '' ? '' : 'You are at: '.Breadcrumb::render(explode('/', $folder), '/'.$folder, $this->urls),
            'site_desc' => $this->settings['site_desc'],
            'og_title' => $title,
            'og_type' => 'website',
            'meta_description' => $this->settings['site_desc'],
            'canonical_url' => $this->settings['origin'].$this->urls->page($folder, $page),
            'entries' => $entries,
            'pages' => $pager->haveToPaginate() ? $pages : [],
        ])), ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function post(string $slug): Response
    {
        $post = $this->posts->find($slug);
        if ($post === null) {
            return $this->error(404, 'Not found.');
        }
        $dir = $post->dir();
        $content = $this->markdown->renderFile($post->path);
        return new Response(200, $this->twig->render('view.html.twig', $this->context([
            'basename' => $post->title(),
            'og_title' => $post->title().' - '.$this->settings['site_name'],
            'og_type' => 'article',
            'meta_description' => self::excerpt($content),
            'canonical_url' => $this->settings['origin'].$this->urls->post($post->relPath),
            'nav_breadcrumb' => $dir === '' ? '' : 'You are at: '.Breadcrumb::render(explode('/', $dir), '/'.$dir, $this->urls),
            'date_str' => date('Y M d (D) H:i', $post->date()),
            'content' => $content,
            'comments' => $this->settings['comments'],
            'source_url' => $this->urls->asset($this->settings['content_dir'].'/'.$post->relPath),
        ])), ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function feed(): Response
    {
        // Feed readers need absolute links, so render with a Urls that includes the origin.
        $absolute = new Urls($this->settings['origin'].$this->urls->base(), constant('EXT_ALLOWED'));
        $markdown = new Markdown($absolute);
        $entries = [];
        foreach (array_slice($this->posts->all(), 0, $this->settings['feed_items']) as $post) {
            $entries[] = [
                'title' => $post->title(),
                'url' => $absolute->post($post->relPath),
                'updated' => $post->date(),
                'summary' => '',
                'content' => $markdown->renderFile($post->path),
            ];
        }
        $xml = Feed::atom(
            $this->settings['site_name'],
            $this->settings['site_desc'],
            $absolute->home(),
            $absolute->feed(),
            $entries
        );
        return new Response(200, $xml, ['Content-Type' => 'application/atom+xml; charset=UTF-8']);
    }

    private function sitemap(): Response
    {
        $absolute = new Urls($this->settings['origin'].$this->urls->base(), constant('EXT_ALLOWED'));
        $posts = $this->posts->all();
        $pages = [['url' => $absolute->home(), 'updated' => isset($posts[0]) ? $posts[0]->date() : 0]];
        foreach ($posts as $post) {
            $pages[] = ['url' => $absolute->post($post->relPath), 'updated' => $post->date()];
        }
        return new Response(200, Feed::sitemap($pages), ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /** Plain-text teaser of some HTML, for <meta name="description">. */
    private static function excerpt(string $html, int $width = 200): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5)));
        return mb_strimwidth($text, 0, $width, '…');
    }

    private function error(int $status, string $message): Response
    {
        return new Response($status, $this->twig->render('error.html.twig', $this->context([
            'message' => $message,
            'status' => $status,
        ])), ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Variables every template needs.
     *
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private function context(array $extra): array
    {
        return $extra + [
            'base' => $this->urls->base(),
            'home_url' => $this->urls->home(),
            'feed_url' => $this->settings['origin'].$this->urls->feed(),
            'site_name' => $this->settings['site_name'],
            'analytics_domain' => $this->settings['analytics_domain'],
            'analytics_script_url' => $this->settings['analytics_script_url'],
        ];
    }
}
