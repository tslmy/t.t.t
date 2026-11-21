<?php

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\TestCase;

require_once "src/commons.php";

#[CoversFunction('truncate_text')]
#[CoversFunction('internal_link')]
#[CoversFunction('get_content')]
#[CoversFunction('print_breadcrumb')]
final class ContentTest extends TestCase
{
    public function testTruncateLeavesShortTextAlone(): void
    {
        $this->assertSame('hello', truncate_text('hello', 10));
    }
    public function testTruncateDoesNotSplitMultibyteCharacters(): void
    {
        $got = truncate_text('ééééé', 5); // 2 bytes each
        $this->assertSame('éé', $got);
        $this->assertTrue(mb_check_encoding($got, 'UTF-8'));
    }
    public function testTruncatePrefersParagraphBreak(): void
    {
        $this->assertSame('one two', truncate_text("one two\n\nthree four", 15));
    }
    public function testInternalLinkEncodesTarget(): void
    {
        $this->assertSame(
            '[label](view.php?name=a%20b%22%3E%3Cx%29)',
            internal_link('a b"><x)', 'label')
        );
    }
    public function testGetContentRendersMarkdownAndLinks(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'ttt');
        file_put_contents($file, "# Hi\n\nSee ~foo/bar.md:this~ and ~baz.md~. <script>alert(1)</script> ~~gone~~\n");
        $html = get_content($file, 0);
        unlink($file);
        $this->assertStringContainsString('<h1>Hi</h1>', $html);
        $this->assertStringContainsString('<a href="view.php?name=foo%2Fbar.md">this</a>', $html);
        $this->assertStringContainsString('<a href="view.php?name=baz.md">baz.md</a>', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('<del>gone</del>', $html);
    }
    public function testBreadcrumbEscapesFolderNames(): void
    {
        $got = print_breadcrumb(['<b>'], '');
        $this->assertStringNotContainsString('<b>', $got);
        $this->assertStringContainsString('&lt;b&gt;', $got);
    }
}
