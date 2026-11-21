<?php

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\TestCase;

require_once "src/commons.php";

#[CoversFunction('resolve_inside')]
final class ResolveInsideTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = realpath(sys_get_temp_dir()).'/ttt-test-'.uniqid();
        mkdir($this->root.'/content/sub', 0777, true);
        mkdir($this->root.'/content-secret', 0777, true);
        touch($this->root.'/content/a.md');
        touch($this->root.'/content/sub/b.md');
        touch($this->root.'/content-secret/s.md');
    }

    protected function tearDown(): void
    {
        foreach ([
            '/content/sub/b.md', '/content/a.md', '/content-secret/s.md',
            '/content/sub', '/content', '/content-secret',
        ] as $p) {
            is_dir($this->root.$p) ? rmdir($this->root.$p) : unlink($this->root.$p);
        }
        rmdir($this->root);
    }
    public function testResolvesFilesAndFolders(): void
    {
        $base = $this->root.'/content';
        $this->assertSame($base, resolve_inside($base, ''));
        $this->assertSame($base.'/a.md', resolve_inside($base, 'a.md'));
        $this->assertSame($base.'/sub', resolve_inside($base, 'sub'));
        $this->assertSame($base.'/sub/b.md', resolve_inside($base, 'sub/../sub/b.md'));
    }
    public function testRejectsTraversal(): void
    {
        $base = $this->root.'/content';
        $this->assertNull(resolve_inside($base, '..'));
        $this->assertNull(resolve_inside($base, '../content-secret/s.md'));
        $this->assertNull(resolve_inside($base, '/../content-secret'));
    }
    public function testMissingPathIsNull(): void
    {
        $this->assertNull(resolve_inside($this->root.'/content', 'nope.md'));
    }
}
