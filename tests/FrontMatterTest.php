<?php

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\TestCase;

require_once "src/commons.php";

#[CoversFunction('split_front_matter')]
#[CoversFunction('read_post_meta')]
#[CoversFunction('post_title')]
#[CoversFunction('post_date')]
#[CoversFunction('post_summary')]
#[CoversFunction('post_is_draft')]
#[CoversFunction('get_content')]
#[CoversFunction('render_markdown')]
#[CoversFunction('internal_link')]
#[CoversFunction('truncate_text')]
final class FrontMatterTest extends TestCase
{
    public function testSplitsHeaderFromBody(): void
    {
        [$meta, $body] = split_front_matter("---\ntitle: Hello\ndraft: true\n---\n# Body\n");
        $this->assertSame('Hello', $meta['title']);
        $this->assertTrue($meta['draft']);
        $this->assertSame("# Body\n", $body);
    }

    public function testNoHeaderLeavesTextAlone(): void
    {
        $this->assertSame([[], "just text\n"], split_front_matter("just text\n"));
        $this->assertSame([[], "---\nnot closed\n"], split_front_matter("---\nnot closed\n"));
        $this->assertSame([[], "a\n---\ntitle: x\n---\n"], split_front_matter("a\n---\ntitle: x\n---\n"), 'only at the very top');
    }

    public function testMalformedYamlIsIgnoredNotFatal(): void
    {
        $text = "---\ntitle: [unclosed\n---\nbody\n";
        $this->assertSame([[], $text], split_front_matter($text));
        $text = "---\njust a string\n---\nbody\n";
        $this->assertSame([[], $text], split_front_matter($text), 'a header must be a mapping');
    }

    public function testHandlesBomWindowsLineEndingsAndEmptyBody(): void
    {
        [$meta, $body] = split_front_matter("\u{FEFF}---\r\ntitle: Win\r\n---\r\nbody\r\n");
        $this->assertSame('Win', $meta['title']);
        $this->assertSame("body\r\n", $body);
        $this->assertSame([['title' => 'x'], ''], split_front_matter("---\ntitle: x\n---"));
    }

    public function testTitle(): void
    {
        $this->assertSame('Pretty', post_title(['title' => ' Pretty '], 'essay/file.md'));
        $this->assertSame('file', post_title([], 'essay/file.md'));
        $this->assertSame('file', post_title(['title' => '  '], 'essay/file.md'));
        $this->assertSame('file', post_title(['title' => ['a', 'b']], 'essay/file.md'));
    }

    public function testDate(): void
    {
        [$meta] = split_front_matter("---\ndate: 2024-05-01\n---\n");
        $this->assertSame(strtotime('2024-05-01 UTC'), post_date($meta, 5));
        $this->assertSame(strtotime('2024-05-01 09:30'), post_date(['date' => '2024-05-01 09:30'], 5));
        $this->assertSame(1700000000, post_date(['date' => 1700000000], 5));
        $this->assertSame(5, post_date(['date' => 'not a date'], 5));
        $this->assertSame(5, post_date(['date' => ['x']], 5));
        $this->assertSame(5, post_date([], 5));
    }

    public function testSummaryAndDraft(): void
    {
        $this->assertSame('Short *one*.', post_summary(['summary' => ' Short *one*. ']));
        $this->assertSame('', post_summary(['summary' => ['x']]));
        $this->assertSame('', post_summary([]));
        $this->assertTrue(post_is_draft(['draft' => true]));
        $this->assertTrue(post_is_draft(['draft' => 'yes']));
        $this->assertFalse(post_is_draft(['draft' => false]));
        $this->assertFalse(post_is_draft(['draft' => 'no']));
        $this->assertFalse(post_is_draft([]));
    }

    public function testReadPostMetaAndContentIgnoreTheHeader(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'ttt');
        file_put_contents($file, "---\ntitle: T\nsummary: S\n---\n# Heading\n\nSee ~baz.md~ and ~~gone~~.\n");
        $this->assertSame('T', read_post_meta($file)['title']);

        $html = get_content($file, 0);
        $this->assertStringContainsString('<h1>Heading</h1>', $html);
        $this->assertStringNotContainsString('summary', $html, 'the header is not rendered');
        $this->assertStringContainsString('<a href="view.php?name=baz.md">baz.md</a>', $html);
        $this->assertStringContainsString('<del>gone</del>', $html);

        file_put_contents($file, "no header here\n");
        $this->assertSame([], read_post_meta($file));
        file_put_contents($file, "---not a header\n");
        $this->assertSame([], read_post_meta($file));
        unlink($file);
    }

    public function testRenderMarkdownStripsHtml(): void
    {
        $this->assertStringNotContainsString('<script>', render_markdown("hi <script>alert(1)</script>"));
    }
}
