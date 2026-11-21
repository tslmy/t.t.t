<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Ttt\Markdown;
use Ttt\Urls;

#[CoversClass(Markdown::class)]
#[UsesClass(Urls::class)]
final class MarkdownTest extends TestCase
{
    private Markdown $md;

    protected function setUp(): void
    {
        $this->md = new Markdown(new Urls('/blog'));
    }

    public function testTruncateLeavesShortTextAlone(): void
    {
        $this->assertSame('hello', Markdown::truncate('hello', 10));
    }

    public function testTruncateDoesNotSplitMultibyteCharacters(): void
    {
        $got = Markdown::truncate('ééééé', 5); // 2 bytes each
        $this->assertSame('éé', $got);
        $this->assertTrue(mb_check_encoding($got, 'UTF-8'));
    }

    public function testTruncatePrefersParagraphBreak(): void
    {
        $this->assertSame('one two', Markdown::truncate("one two\n\nthree four", 15));
    }

    public function testInternalLinkEncodesTarget(): void
    {
        $this->assertSame('[label](/blog/post/a%20b%22%3E%3Cx%29)', $this->md->internalLink('a b"><x)', 'label'));
    }

    public function testRendersMarkdownLinksAndStripsHtml(): void
    {
        $html = $this->md->render("# Hi\n\nSee ~foo/bar.md:this~ and ~baz.md~. <script>alert(1)</script> ~~gone~~\n");
        $this->assertStringContainsString('<h1>Hi</h1>', $html);
        $this->assertStringContainsString('<a href="/blog/post/foo/bar">this</a>', $html);
        $this->assertStringContainsString('<a href="/blog/post/baz">baz.md</a>', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('<del>gone</del>', $html);
    }

    public function testRenderFileHonoursTheSizeLimit(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'ttt');
        file_put_contents($file, "first\n\nsecond paragraph\n");
        $this->assertStringNotContainsString('second', $this->md->renderFile($file, 8));
        $this->assertStringContainsString('second', $this->md->renderFile($file));
        unlink($file);
    }
}
