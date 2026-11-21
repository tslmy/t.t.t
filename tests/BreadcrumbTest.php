<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Ttt\Breadcrumb;
use Ttt\Urls;

#[CoversClass(Breadcrumb::class)]
#[UsesClass(Urls::class)]
final class BreadcrumbTest extends TestCase
{
    #[DataProvider('cases')]
    public function testOutputs(array $crumbs, string $cur, string $want): void
    {
        $this->assertSame($want, Breadcrumb::render($crumbs, $cur, new Urls()));
    }

    public static function cases(): array
    {
        return [
            [['a', 'b', 'c'], '/a/b',
                '<a href="/">Home</a> &gt; <a href="/folder/a">a</a> &gt; b &gt; <a href="/folder/a/b/c">c</a>'],
            [['a', 'b', 'c'], '',
                '<a href="/">Home</a> &gt; <a href="/folder/a">a</a> &gt; <a href="/folder/a/b">b</a> &gt; <a href="/folder/a/b/c">c</a>'],
            [[], '', '<a href="/">Home</a>'],
            [[], '/a/b', '<a href="/">Home</a>'],
            [['<b>'], '', '<a href="/">Home</a> &gt; <a href="/folder/%3Cb%3E">&lt;b&gt;</a>'],
        ];
    }
}
