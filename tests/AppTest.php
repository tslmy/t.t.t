<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Ttt\App;
use Ttt\Breadcrumb;
use Ttt\Feed;
use Ttt\Markdown;
use Ttt\Post;
use Ttt\PostRepository;
use Ttt\Response;
use Ttt\Urls;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

#[CoversClass(App::class)]
#[CoversClass(Response::class)]
#[CoversClass(Feed::class)]
#[UsesClass(Urls::class)]
#[UsesClass(Post::class)]
#[UsesClass(PostRepository::class)]
#[UsesClass(Markdown::class)]
#[UsesClass(Breadcrumb::class)]
final class AppTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = realpath(sys_get_temp_dir()).'/ttt-app-'.uniqid();
        mkdir($this->root.'/essays', 0777, true);
        foreach (['one' => 1000, 'two' => 2000, 'three' => 3000] as $name => $mtime) {
            file_put_contents($this->root."/$name.md", "# $name\n\nbody of $name\n");
            touch($this->root."/$name.md", $mtime);
        }
        file_put_contents($this->root.'/essays/deep.md', "deep <script>x</script>\n");
        touch($this->root.'/essays/deep.md', 500);
    }

    protected function tearDown(): void
    {
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->root, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        ) as $f) {
            $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
        }
        rmdir($this->root);
    }

    private function app(string $base = '', int $perPage = 2): App
    {
        $urls = new Urls($base);
        return new App(
            new PostRepository($this->root, ['md', 'txt']),
            $urls,
            new Markdown($urls),
            new Environment(new FilesystemLoader(dirname(__DIR__).'/templates')),
            [
                'site_name' => 'Test <Site>',
                'site_desc' => 'desc',
                'per_page' => $perPage,
                'preview_bytes' => 1000,
                'content_dir' => 'content',
                'origin' => 'https://example.org',
                'feed_items' => 2,
                'disqus' => '',
                'analytics_domain' => '',
                'analytics_script_url' => '',
            ]
        );
    }

    public function testIndexListsNewestFirstAndPaginates(): void
    {
        $r = $this->app()->handle('/');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('Test &lt;Site&gt;', $r->body);
        $this->assertStringContainsString('href="/post/three"', $r->body);
        $this->assertStringContainsString('href="/post/two"', $r->body);
        $this->assertStringNotContainsString('href="/post/one"', $r->body);
        $this->assertStringContainsString('href="/?page=2"', $r->body);

        $page2 = $this->app()->handle('/?page=2', ['page' => '2']);
        $this->assertStringContainsString('href="/post/one"', $page2->body);
        $this->assertStringNotContainsString('href="/post/three"', $page2->body);
    }

    public function testOutOfRangeAndJunkPagesAreClamped(): void
    {
        foreach (['99', '0', '-4', 'abc'] as $p) {
            $this->assertSame(200, $this->app()->handle('/', ['page' => $p])->status, $p);
        }
        $this->assertSame(200, $this->app()->handle('/', ['page' => ['x']])->status);
    }

    public function testFolderListing(): void
    {
        $r = $this->app()->handle('/folder/essays');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('href="/post/essays/deep"', $r->body);
        $this->assertStringNotContainsString('href="/post/three"', $r->body);
        $this->assertSame(404, $this->app()->handle('/folder/missing')->status);
        $this->assertSame(404, $this->app()->handle('/folder/..')->status);
    }

    public function testPostPage(): void
    {
        $r = $this->app()->handle('/post/essays/deep');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('deep', $r->body);
        $this->assertStringNotContainsString('<script>x', $r->body, 'raw HTML in posts is stripped');
        $this->assertStringContainsString('href="/content/essays/deep.md"', $r->body);
        $this->assertSame(404, $this->app()->handle('/post/nope')->status);
        $this->assertSame(404, $this->app()->handle('/post/../../etc/passwd')->status);
        $this->assertSame(404, $this->app()->handle('/post/essays')->status);
    }

    public function testOldUrlsRedirectPermanently(): void
    {
        $r = $this->app()->handle('/view.php?name=essays%2Fdeep.md', ['name' => 'essays/deep.md']);
        $this->assertSame(301, $r->status);
        $this->assertSame('/post/essays/deep', $r->headers['Location']);

        $r = $this->app()->handle('/index.php?folder=%2Fessays', ['folder' => '/essays']);
        $this->assertSame(301, $r->status);
        $this->assertSame('/folder/essays', $r->headers['Location']);

        $this->assertSame(400, $this->app()->handle('/view.php')->status);
        $this->assertSame(200, $this->app()->handle('/index.php')->status);
    }

    public function testUnknownRoutesAre404(): void
    {
        $r = $this->app()->handle('/wp-login.php');
        $this->assertSame(404, $r->status);
        $this->assertStringContainsString('Not found.', $r->body);
    }

    public function testSubdirectoryInstall(): void
    {
        $r = $this->app('/blog')->handle('/blog/');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('href="/blog/post/three"', $r->body);
        $this->assertStringContainsString('href="/blog/vendor/mvp.css"', $r->body);
        $this->assertSame(200, $this->app('/blog')->handle('/blog/post/one')->status);
        $this->assertSame(404, $this->app('/blog')->handle('/post/one')->status);
    }

    public function testAtomFeed(): void
    {
        file_put_contents($this->root.'/three.md', "See ~one~. A \x0b control & <b>tag</b>\n");
        touch($this->root.'/three.md', 3000);
        $r = $this->app('/blog')->handle('/blog/feed.xml');
        $this->assertSame(200, $r->status);
        $this->assertStringStartsWith('application/atom+xml', $r->headers['Content-Type']);

        $xml = simplexml_load_string($r->body);  // must be well-formed despite the control character
        $this->assertNotFalse($xml);
        $this->assertSame('Test <Site>', (string) $xml->title);
        $this->assertCount(2, $xml->entry, 'limited to feed_items');
        $this->assertSame('https://example.org/blog/post/three', (string) $xml->entry[0]->id);
        $this->assertSame('https://example.org/blog/post/two', (string) $xml->entry[1]->id);
        $this->assertStringContainsString('href="https://example.org/blog/post/one"', (string) $xml->entry[0]->content, 'links in entries are absolute');
        $this->assertSame(gmdate(DATE_ATOM, 3000), (string) $xml->updated);
    }

    public function testSitemapListsEveryPostAndHome(): void
    {
        $xml = simplexml_load_string($this->app()->handle('/sitemap.xml')->body);
        $this->assertNotFalse($xml);
        $locs = [];
        foreach ($xml->url as $u) {
            $locs[] = (string) $u->loc;
        }
        $this->assertSame([
            'https://example.org/',
            'https://example.org/post/three',
            'https://example.org/post/two',
            'https://example.org/post/one',
            'https://example.org/post/essays/deep',
        ], $locs);
    }

    public function testEmptySiteStillProducesValidXml(): void
    {
        $empty = realpath(sys_get_temp_dir()).'/ttt-empty-'.uniqid();
        mkdir($empty);
        $urls = new Urls();
        $app = new App(
            new PostRepository($empty, ['md']),
            $urls,
            new Markdown($urls),
            new Environment(new FilesystemLoader(dirname(__DIR__).'/templates')),
            ['site_name' => 'S', 'site_desc' => '', 'per_page' => 2, 'preview_bytes' => 10, 'content_dir' => 'c',
             'origin' => 'https://e.org', 'feed_items' => 5, 'disqus' => '', 'analytics_domain' => '', 'analytics_script_url' => '']
        );
        $this->assertNotFalse(simplexml_load_string($app->handle('/feed.xml')->body));
        $this->assertNotFalse(simplexml_load_string($app->handle('/sitemap.xml')->body));
        $this->assertSame(200, $app->handle('/')->status);
        rmdir($empty);
    }

    public function testPagesCarryMetaTags(): void
    {
        $post = $this->app()->handle('/post/three')->body;
        $this->assertStringContainsString('<meta name="description" content="three body of three"', $post);
        $this->assertStringContainsString('<link rel="canonical" href="https://example.org/post/three"', $post);
        $this->assertStringContainsString('property="og:type" content="article"', $post);
        $this->assertStringContainsString('<link rel="alternate" type="application/atom+xml"', $post);
        $this->assertStringContainsString('href="https://example.org/feed.xml"', $post);

        $home = $this->app()->handle('/')->body;
        $this->assertStringContainsString('<meta name="description" content="desc"', $home);
        $this->assertStringContainsString('property="og:type" content="website"', $home);
    }
}
