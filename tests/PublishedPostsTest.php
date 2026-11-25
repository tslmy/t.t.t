<?php

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\TestCase;

require_once "src/commons.php";

#[CoversFunction('published_posts')]
#[CoversFunction('atom_feed')]
final class PublishedPostsTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = realpath(sys_get_temp_dir()).'/ttt-pub-'.uniqid();
        mkdir($this->dir);
        file_put_contents("$this->dir/plain.md", "plain\n");
        touch("$this->dir/plain.md", 1000);
        file_put_contents("$this->dir/titled.md", "---\ntitle: Pretty\ndate: 2030-01-02 03:04\nsummary: A *short* one.\n---\nbody\n");
        touch("$this->dir/titled.md", 500);  // an old file with a newer front matter date
        file_put_contents("$this->dir/wip.md", "---\ndraft: true\ndate: 2040-01-01\n---\nsecret\n");
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob("$this->dir/*"));
        rmdir($this->dir);
    }

    public function testDraftsAreExcludedAndFrontMatterDrivesOrderTitleAndSummary(): void
    {
        $posts = published_posts($this->dir, ['md']);
        $this->assertSame(['titled.md', 'plain.md'], array_column($posts, 'rel'), 'no draft, front matter date wins over mtime');
        $this->assertSame('Pretty', $posts[0]['title']);
        $this->assertSame('plain', $posts[1]['title']);
        $this->assertSame(strtotime('2030-01-02 03:04'), $posts[0]['date']);
        $this->assertSame(1000, $posts[1]['date']);
        $this->assertSame('A *short* one.', $posts[0]['summary']);
    }

    public function testDraftsAppearWhenAsked(): void
    {
        $this->assertSame(['wip.md', 'titled.md', 'plain.md'], array_column(published_posts($this->dir, ['md'], true), 'rel'));
    }

    public function testFeedEntryCanCarryASummary(): void
    {
        $feed = simplexml_load_string(atom_feed('S', '', 'https://a.org/', 'https://a.org/feed.php', [
            ['title' => 'A', 'url' => 'https://a.org/a', 'updated' => 5, 'content' => '<p>full</p>', 'summary' => '<p>short</p>'],
            ['title' => 'B', 'url' => 'https://a.org/b', 'updated' => 4, 'content' => '<p>full</p>'],
        ]));
        $this->assertNotFalse($feed);
        $this->assertSame('<p>short</p>', (string) $feed->entry[0]->summary);
        $this->assertCount(0, $feed->entry[1]->summary, 'no summary element when there is none');
    }
}
