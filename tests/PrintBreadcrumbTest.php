<?php

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once "src/commons.php";

#[CoversFunction('print_breadcrumb')]
final class PrintBreadcrumbTest extends TestCase
{
    #[DataProvider('breadcrumbTestDataProvider')]
    public function testOutputs($crumbs, $cur_path, $want): void
    {
        $got = print_breadcrumb($crumbs, $cur_path);
        $this->assertEquals($want, $got);
    }
    public static function breadcrumbTestDataProvider(): array
    {
        return [
            [
                ['a', 'b', 'c'],
                '/a/b',
                '<a href="index.php">Home</a> &gt; <a href="index.php?folder=%2Fa">a</a> &gt; b &gt; <a href="index.php?folder=%2Fa%2Fb%2Fc">c</a>',
            ],
            [
                ['a', 'b', 'c'],
                '',
                '<a href="index.php">Home</a> &gt; <a href="index.php?folder=%2Fa">a</a> &gt; <a href="index.php?folder=%2Fa%2Fb">b</a> &gt; <a href="index.php?folder=%2Fa%2Fb%2Fc">c</a>',
            ],
            [
                [],
                '',
                '<a href="index.php">Home</a>',
            ],
            [
                [],
                '/a/b',
                '<a href="index.php">Home</a>',
            ],
        ];
    }
}
