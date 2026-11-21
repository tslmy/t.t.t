<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ttt\FrontMatter;

#[CoversClass(FrontMatter::class)]
final class FrontMatterTest extends TestCase
{
    public function testSplitsHeaderFromBody(): void
    {
        [$meta, $body] = FrontMatter::split("---\ntitle: Hello\ndraft: true\n---\n# Body\n");
        $this->assertSame('Hello', $meta['title']);
        $this->assertTrue($meta['draft']);
        $this->assertSame("# Body\n", $body);
    }

    public function testNoHeaderLeavesTextAlone(): void
    {
        $this->assertSame([[], "just text\n"], FrontMatter::split("just text\n"));
        $this->assertSame([[], "---\nnot closed\n"], FrontMatter::split("---\nnot closed\n"));
        $this->assertSame([[], "a\n---\ntitle: x\n---\n"], FrontMatter::split("a\n---\ntitle: x\n---\n"), 'only at the very top');
    }

    public function testMalformedYamlIsIgnoredNotFatal(): void
    {
        $text = "---\ntitle: [unclosed\n---\nbody\n";
        $this->assertSame([[], $text], FrontMatter::split($text));
        $text = "---\njust a string\n---\nbody\n";
        $this->assertSame([[], $text], FrontMatter::split($text), 'a header must be a mapping');
    }

    public function testHandlesBomWindowsLineEndingsAndEmptyBody(): void
    {
        [$meta, $body] = FrontMatter::split("\u{FEFF}---\r\ntitle: Win\r\n---\r\nbody\r\n");
        $this->assertSame('Win', $meta['title']);
        $this->assertSame("body\r\n", $body);
        $this->assertSame([['title' => 'x'], ''], FrontMatter::split("---\ntitle: x\n---"));
    }

    public function testTimestamps(): void
    {
        [$meta] = FrontMatter::split("---\ndate: 2024-05-01\n---\n");
        $this->assertSame(strtotime('2024-05-01 UTC'), FrontMatter::timestamp($meta['date']));
        $this->assertSame(strtotime('2024-05-01 09:30'), FrontMatter::timestamp('2024-05-01 09:30'));
        $this->assertSame(1700000000, FrontMatter::timestamp(1700000000));
        $this->assertNull(FrontMatter::timestamp('not a date'));
        $this->assertNull(FrontMatter::timestamp(null));
        $this->assertNull(FrontMatter::timestamp(['x']));
    }
}
