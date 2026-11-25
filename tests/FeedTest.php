<?php

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\TestCase;

require_once "src/commons.php";

#[CoversFunction('site_origin')]
#[CoversFunction('absolute_url')]
#[CoversFunction('list_post_files')]
#[CoversFunction('html_excerpt')]
#[CoversFunction('xml_clean')]
#[CoversFunction('atom_feed')]
#[CoversFunction('sitemap_xml')]
final class FeedTest extends TestCase
{
    public function testOrigin(): void
    {
        $this->assertSame('https://blog.example:8443', site_origin([], 'https://blog.example:8443/some/path/'));
        $this->assertSame('http://localhost', site_origin([], 'javascript:alert(1)'), 'invalid config is ignored');
        $this->assertSame('https://a.org', site_origin(['HTTPS' => 'on', 'HTTP_HOST' => 'a.org']));
        $this->assertSame('http://a.org:8080', site_origin(['HTTPS' => 'off', 'HTTP_HOST' => 'a.org:8080']));
        $this->assertSame('http://localhost', site_origin(['HTTP_HOST' => 'evil.com/<script>']), 'hostile Host header');
        $this->assertSame('https://configured.org', site_origin(['HTTP_HOST' => 'other.org'], 'https://configured.org'));
    }

    public function testAbsoluteUrlHandlesSubdirectories(): void
    {
        $root = ['HTTP_HOST' => 'a.org', 'SCRIPT_NAME' => '/feed.php'];
        $this->assertSame('http://a.org/', absolute_url($root));
        $this->assertSame('http://a.org/view.php?name=x.md', absolute_url($root, 'view.php?name=x.md'));
        $sub = ['HTTP_HOST' => 'a.org', 'SCRIPT_NAME' => '/blog/feed.php'];
        $this->assertSame('http://a.org/blog/', absolute_url($sub));
        $this->assertSame('http://a.org/blog/feed.php', absolute_url($sub, '/feed.php'));
    }

    public function testListPostFilesNewestFirst(): void
    {
        $dir = realpath(sys_get_temp_dir()).'/ttt-feed-'.uniqid();
        mkdir($dir.'/sub', 0777, true);
        foreach (['old.md' => 100, 'sub/new.txt' => 300, 'mid.md' => 200] as $name => $mtime) {
            file_put_contents("$dir/$name", 'x');
            touch("$dir/$name", $mtime);
        }
        file_put_contents("$dir/ignored.png", 'x');
        $got = list_post_files($dir, ['md', 'txt']);
        $this->assertSame(['sub/new.txt', 'mid.md', 'old.md'], array_column($got, 'rel'));
        $this->assertSame(300, $got[0]['mtime']);
        foreach (['old.md', 'sub/new.txt', 'mid.md', 'ignored.png'] as $name) {
            unlink("$dir/$name");
        }
        rmdir($dir.'/sub');
        rmdir($dir);
    }

    public function testExcerpt(): void
    {
        $this->assertSame('Hello & goodbye', html_excerpt("<p>Hello &amp;\n  goodbye</p>"));
        $this->assertSame(10, mb_strlen(html_excerpt('<p>'.str_repeat('é', 50).'</p>', 10)));
        $this->assertStringEndsWith('…', html_excerpt(str_repeat('word ', 100), 20));
    }

    public function testAtomFeedIsWellFormedEvenWithControlCharacters(): void
    {
        $xml = atom_feed('Site <&>', 'sub', 'https://a.org/', 'https://a.org/feed.php', [
            ['title' => "A \x0b title", 'url' => 'https://a.org/view.php?name=a.md', 'updated' => 3000, 'content' => "<p>x \x0b &amp; <b>y</b></p>"],
            ['title' => 'B', 'url' => 'https://a.org/view.php?name=b.md', 'updated' => 1000, 'content' => '<p>b</p>'],
        ]);
        $feed = simplexml_load_string($xml);
        $this->assertNotFalse($feed);
        $this->assertSame('Site <&>', (string) $feed->title);
        $this->assertSame(gmdate(DATE_ATOM, 3000), (string) $feed->updated);
        $this->assertCount(2, $feed->entry);
        $this->assertSame('A  title', (string) $feed->entry[0]->title);
        $this->assertSame('<p>x  &amp; <b>y</b></p>', (string) $feed->entry[0]->content);
    }

    public function testEmptyFeedAndSitemapAreStillValidXml(): void
    {
        $this->assertNotFalse(simplexml_load_string(atom_feed('S', '', 'https://a.org/', 'https://a.org/feed.php', [])));
        $this->assertNotFalse(simplexml_load_string(sitemap_xml([])));
    }

    public function testSitemap(): void
    {
        $xml = simplexml_load_string(sitemap_xml([
            ['url' => 'https://a.org/', 'updated' => 2000],
            ['url' => 'https://a.org/view.php?name=a%20b.md&x=1', 'updated' => 1000],
        ]));
        $this->assertNotFalse($xml);
        $this->assertCount(2, $xml->url);
        $this->assertSame('https://a.org/view.php?name=a%20b.md&x=1', (string) $xml->url[1]->loc);
        $this->assertSame(gmdate(DATE_ATOM, 1000), (string) $xml->url[1]->lastmod);
    }
}
