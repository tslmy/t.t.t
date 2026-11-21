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
}
