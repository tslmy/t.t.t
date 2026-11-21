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
 *   view.php?name=, index.php?folder=   old URLs, redirected permanently
 */
final class App
{
    /**
     * @param array{site_name: string, site_desc: string, per_page: int, preview_bytes: int, content_dir: string, disqus: string, analytics_domain: string, analytics_script_url: string} $settings
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
                'disqus' => constant('DISQUS_SHORTNAME'),
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
        return new Response(200, $this->twig->render('view.html.twig', $this->context([
            'basename' => $post->title(),
            'nav_breadcrumb' => $dir === '' ? '' : 'You are at: '.Breadcrumb::render(explode('/', $dir), '/'.$dir, $this->urls),
            'date_str' => date('Y M d (D) H:i', $post->date()),
            'content' => $this->markdown->renderFile($post->path),
            'disqus' => $this->settings['disqus'],
            'source_url' => $this->urls->asset($this->settings['content_dir'].'/'.$post->relPath),
        ])), ['Content-Type' => 'text/html; charset=UTF-8']);
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
            'site_name' => $this->settings['site_name'],
            'analytics_domain' => $this->settings['analytics_domain'],
            'analytics_script_url' => $this->settings['analytics_script_url'],
        ];
    }
}
