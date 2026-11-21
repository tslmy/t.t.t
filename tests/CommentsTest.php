<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Ttt\Comments;

#[CoversClass(Comments::class)]
final class CommentsTest extends TestCase
{
    public function testFullyConfigured(): void
    {
        $this->assertSame(
            ['repo' => 'me/blog.x', 'repo_id' => 'R_1', 'category' => 'General', 'category_id' => 'DIC_1'],
            Comments::giscus(' me/blog.x ', 'R_1', 'General', 'DIC_1')
        );
    }

    public function testAnyMissingPieceDisablesComments(): void
    {
        $this->assertNull(Comments::giscus('', '', '', ''));
        $this->assertNull(Comments::giscus('me/blog', '', 'General', 'DIC_1'));
        $this->assertNull(Comments::giscus('me/blog', 'R_1', '  ', 'DIC_1'));
        $this->assertNull(Comments::giscus('me/blog', 'R_1', 'General', ''));
    }

    public function testRepositoryMustLookLikeOwnerSlashName(): void
    {
        foreach (['blog', 'me/', '/blog', 'me/blog/extra', 'me/bl og', 'me/"><x'] as $bad) {
            $this->assertNull(Comments::giscus($bad, 'R_1', 'General', 'DIC_1'), $bad);
        }
    }
}
