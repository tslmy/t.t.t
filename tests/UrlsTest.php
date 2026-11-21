<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ttt\Urls;

#[CoversClass(Urls::class)]
final class UrlsTest extends TestCase
{
    public function testBuildsCleanUrls(): void
    {
        $u = new Urls();
        $this->assertSame('/', $u->home());
        $this->assertSame('/post/essay/title', $u->post('essay/title.md'));
        $this->assertSame('/post/a%20b/c%3Fd', $u->post('a b/c?d.txt'));
        $this->assertSame('/post/archive.v2', $u->post('archive.v2'), 'unknown extensions are kept');
        $this->assertSame('/', $u->folder(''));
        $this->assertSame('/folder/a/b', $u->folder('/a/b/'));
        $this->assertSame('/folder/a', $u->page('a', 1));
        $this->assertSame('/folder/a?page=3', $u->page('a', 3));
        $this->assertSame('/?page=2', $u->page('', 2));
        $this->assertSame('/content/a%20b.md', $u->asset('content/a b.md'));
        $this->assertSame('/blog/feed.xml', (new Urls('/blog'))->feed());
    }

    public function testWorksInASubdirectory(): void
    {
        $u = Urls::fromServer(['SCRIPT_NAME' => '/blog/index.php']);
        $this->assertSame('/blog', $u->base());
        $this->assertSame('/blog/post/x', $u->post('x.md'));
        $this->assertSame('/blog/', $u->home());
        $this->assertSame('post/x', $u->route('/blog/post/x'));
        $this->assertSame('', $u->route('/blog'));
        $this->assertSame('', $u->route('/blog/?page=2'));
        $this->assertNull($u->route('/elsewhere'));
        $this->assertNull($u->route('/blogger/post/x'));
    }

    public function testRootInstallHasNoBase(): void
    {
        $this->assertSame('', Urls::fromServer(['SCRIPT_NAME' => '/index.php'])->base());
        $this->assertSame('', Urls::fromServer([])->base());
    }

    public function testRouteDecodesAndTrims(): void
    {
        $u = new Urls();
        $this->assertSame('post/a b', $u->route('/post/a%20b/?x=1'));
        $this->assertSame('', $u->route('/'));
    }

    public function testOrigin(): void
    {
        $this->assertSame('https://blog.example:8443', Urls::origin([], 'https://blog.example:8443/some/path/'));
        $this->assertSame('http://localhost', Urls::origin([], 'javascript:alert(1)'), 'invalid config is ignored');
        $this->assertSame('https://a.org', Urls::origin(['HTTPS' => 'on', 'HTTP_HOST' => 'a.org']));
        $this->assertSame('http://a.org:8080', Urls::origin(['HTTPS' => 'off', 'HTTP_HOST' => 'a.org:8080']));
        $this->assertSame('http://localhost', Urls::origin(['HTTP_HOST' => 'evil.com/<script>']), 'hostile Host header');
        $this->assertSame('https://configured.org', Urls::origin(['HTTP_HOST' => 'other.org'], 'https://configured.org'));
    }
}
