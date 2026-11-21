<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Ttt\App;
use Ttt\Breadcrumb;
use Ttt\Markdown;
use Ttt\Post;
use Ttt\PostRepository;
use Ttt\Response;
use Ttt\Urls;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

#[CoversClass(App::class)]
#[CoversClass(Response::class)]
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
}
