<?php

declare(strict_types=1);

namespace Joalvm\Tests\Helpers;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class UtilsTest extends TestCase
{
    public function testIsArrayAssoc(): void
    {
        $this->assertTrue(is_array_assoc(['a' => 'b']));
        $this->assertFalse(is_array_assoc(['a', 'b']));
        $this->assertFalse(is_array_assoc('wrong value'));
    }

    public function testIsArrayList(): void
    {
        $this->assertTrue(is_array_list(['a', 'b']));
        $this->assertFalse(is_array_list(['a' => 'b']));
        $this->assertFalse(is_array_list('wrong value'));
    }

    public function testFormatBytes(): void
    {
        $this->assertEquals('0 B', format_bytes(0));
        $this->assertEquals('1 B', format_bytes(1));
        $this->assertEquals('1 KB', format_bytes(1024));
        $this->assertEquals('1 MB', format_bytes(1024 * 1024));
        $this->assertEquals('1 GB', format_bytes(1024 * 1024 * 1024));
        $this->assertEquals('1 TB', format_bytes(1024 * 1024 * 1024 * 1024));
    }

    public function testDot(): void
    {
        $this->assertEquals(['a.b.c' => 'd'], dot(['a' => ['b' => ['c' => 'd']]]));
    }
}
