<?php

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\TestCase;

require_once "src/commons.php";

#[CoversFunction('giscus_settings')]
final class GiscusTest extends TestCase
{
    public function testFullyConfigured(): void
    {
        $this->assertSame(
            ['repo' => 'me/blog.x', 'repo_id' => 'R_1', 'category' => 'General', 'category_id' => 'DIC_1'],
            giscus_settings(' me/blog.x ', 'R_1', 'General', 'DIC_1')
        );
    }

    public function testAnyMissingPieceDisablesComments(): void
    {
        $this->assertNull(giscus_settings('', '', '', ''));
        $this->assertNull(giscus_settings('me/blog', '', 'General', 'DIC_1'));
        $this->assertNull(giscus_settings('me/blog', 'R_1', '  ', 'DIC_1'));
        $this->assertNull(giscus_settings('me/blog', 'R_1', 'General', ''));
    }

    public function testRepositoryMustLookLikeOwnerSlashName(): void
    {
        foreach (['blog', 'me/', '/blog', 'me/blog/extra', 'me/bl og', 'me/"><x'] as $bad) {
            $this->assertNull(giscus_settings($bad, 'R_1', 'General', 'DIC_1'), $bad);
        }
    }
}
