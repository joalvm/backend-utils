<?php

namespace Joalvm\Tests\Helpers;

use PHPUnit\Framework\TestCase;

/**
 * Casts para los helpers del archivo casts.php.
 *
 * @internal
 *
 * @coversNothing
 */
class CastTest extends TestCase
{
    public function testCastAssocInt(): void
    {
        $item = ['a' => '1', 'b' => '2', 'c' => '3'];

        cast_assoc_int($item, ['a', 'b']);

        $this->assertSame(['a' => 1, 'b' => 2, 'c' => '3'], $item);
    }

    public function testCastAssocFloat(): void
    {
        $item = ['a' => '1.1', 'b' => '2.2', 'c' => '3.3'];

        cast_assoc_float($item, ['a', 'b']);

        $this->assertSame(['a' => 1.1, 'b' => 2.2, 'c' => '3.3'], $item);
    }

    public function testCastAssocFloatWithPrecision(): void
    {
        $item = ['a' => '1.111', 'b' => '2.222', 'c' => '3.333'];

        cast_assoc_float($item, ['a', 'b'], 2);

        $this->assertSame(['a' => 1.11, 'b' => 2.22, 'c' => '3.333'], $item);
    }

    public function testCastAssocFloatWithModeRoundHalfUp(): void
    {
        $item = ['a' => '1.5', 'b' => '2.5', 'c' => '3.5'];

        cast_assoc_float($item, ['a', 'b'], 0, PHP_ROUND_HALF_UP);

        $this->assertSame(['a' => 2.0, 'b' => 3.0, 'c' => '3.5'], $item);
    }

    public function testCastAssocFloatWithModeRoundHalfDown(): void
    {
        $item = ['a' => '1.5', 'b' => '2.5', 'c' => '3.5'];

        cast_assoc_float($item, ['a', 'b'], 0, PHP_ROUND_HALF_DOWN);

        $this->assertSame(['a' => 1.0, 'b' => 2.0, 'c' => '3.5'], $item);
    }

    public function testCastAssocFloatWithModelRoundHalfEven(): void
    {
        $item = ['a' => '1.5', 'b' => '2.5', 'c' => '3.5'];

        cast_assoc_float($item, ['a', 'b'], 0, PHP_ROUND_HALF_EVEN);

        $this->assertSame(['a' => 2.0, 'b' => 2.0, 'c' => '3.5'], $item);
    }

    public function testCastAssocFloatWithModelRoundHalfOdd(): void
    {
        $item = ['a' => '1.5', 'b' => '2.5', 'c' => '3.5'];

        cast_assoc_float($item, ['a', 'b'], 0, PHP_ROUND_HALF_ODD);

        $this->assertSame(['a' => 1.0, 'b' => 3.0, 'c' => '3.5'], $item);
    }

    public function testCastAssocNumeric(): void
    {
        $item = ['a' => '1.1', 'b' => '2.2', 'c' => '3.3'];

        cast_assoc_numeric($item, ['a', 'b']);

        $this->assertSame(['a' => 1.1, 'b' => 2.2, 'c' => '3.3'], $item);
    }
}
