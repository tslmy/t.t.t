<?php

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\TestCase;


#[CoversFunction('env_string')]
#[CoversFunction('env_int')]
final class ConfigTest extends TestCase
{
    private const VAR = 'TTT_TEST_SETTING';

    protected function tearDown(): void
    {
        putenv(self::VAR);
    }

    public function testStringFallsBackWhenUnsetOrEmpty(): void
    {
        $this->assertSame('dflt', env_string(self::VAR, 'dflt'));
        putenv(self::VAR.'=');
        $this->assertSame('dflt', env_string(self::VAR, 'dflt'));
    }

    public function testStringReadsEnvironment(): void
    {
        putenv(self::VAR.'=hello');
        $this->assertSame('hello', env_string(self::VAR, 'dflt'));
    }

    public function testIntAcceptsPositiveIntegers(): void
    {
        putenv(self::VAR.'=25');
        $this->assertSame(25, env_int(self::VAR, 10));
    }

    public function testIntRejectsGarbageZeroAndNegatives(): void
    {
        foreach (['abc', '0', '-3', '1.5', ''] as $bad) {
            putenv(self::VAR.'='.$bad);
            $this->assertSame(10, env_int(self::VAR, 10), "for '$bad'");
        }
        putenv(self::VAR);
        $this->assertSame(10, env_int(self::VAR, 10));
    }
}
