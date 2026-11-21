<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Ttt\Post;
use Ttt\PostRepository;

#[CoversClass(PostRepository::class)]
#[UsesClass(Post::class)]
final class PostRepositoryTest extends TestCase
{
    private string $root;
    private PostRepository $repo;

    protected function setUp(): void
    {
        $this->root = realpath(sys_get_temp_dir()).'/ttt-test-'.uniqid();
        mkdir($this->root.'/content/sub', 0777, true);
        mkdir($this->root.'/content-secret', 0777, true);
        file_put_contents($this->root.'/content/a.md', 'a');
        file_put_contents($this->root.'/content/sub/b.txt', 'b');
        file_put_contents($this->root.'/content/ignored.png', 'x');
        file_put_contents($this->root.'/content-secret/s.md', 's');
        touch($this->root.'/content/a.md', 2000);
        touch($this->root.'/content/sub/b.txt', 3000);
        $this->repo = new PostRepository($this->root.'/content', ['md', 'txt']);
    }

    protected function tearDown(): void
    {
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->root, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        ) as $f) {
            $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
        }
        rmdir($this->root);
    }

    public function testResolveInsideStaysInside(): void
    {
        $base = $this->root.'/content';
        $this->assertSame($base, PostRepository::resolveInside($base, ''));
        $this->assertSame($base.'/sub/b.txt', PostRepository::resolveInside($base, 'sub/../sub/b.txt'));
        $this->assertNull(PostRepository::resolveInside($base, '..'));
        $this->assertNull(PostRepository::resolveInside($base, '../content-secret/s.md'), 'sibling with a common prefix');
        $this->assertNull(PostRepository::resolveInside($base, 'nope.md'));
        $this->assertNull(PostRepository::resolveInside($base, "a.md\0.png"));
    }

    public function testFindPostWithoutExtension(): void
    {
        $post = $this->repo->find('sub/b');
        $this->assertNotNull($post);
        $this->assertSame('sub/b.txt', $post->relPath);
        $this->assertSame('sub', $post->dir());
        $this->assertSame('b', $post->title());
        $this->assertSame(3000, $post->date());
        $this->assertNull($this->repo->find('ignored'), 'disallowed extension');
        $this->assertNull($this->repo->find('../content-secret/s'));
        $this->assertNull($this->repo->find('sub'), 'a folder is not a post');
    }

    public function testFolders(): void
    {
        $this->assertSame('', $this->repo->folder(''));
        $this->assertSame('sub', $this->repo->folder('sub'));
        $this->assertNull($this->repo->folder('a.md'));
        $this->assertNull($this->repo->folder('..'));
    }

    public function testAllIsNewestFirstAndRecursive(): void
    {
        $this->assertSame(['sub/b.txt', 'a.md'], array_map(fn (Post $p) => $p->relPath, $this->repo->all()));
        $this->assertSame(['sub/b.txt'], array_map(fn (Post $p) => $p->relPath, $this->repo->all('sub')));
        $this->assertSame([], $this->repo->all('missing'));
    }
}
