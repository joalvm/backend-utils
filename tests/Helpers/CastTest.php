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
    public function test_cast_assoc_int(): void
    {
        $item = ['a' => '1', 'b' => '2', 'c' => '3'];

        cast_assoc_int($item, ['a', 'b']);

        $this->assertSame(['a' => 1, 'b' => 2, 'c' => '3'], $item);
    }

    public function test_cast_assoc_float(): void
    {
        $item = ['a' => '1.1', 'b' => '2.2', 'c' => '3.3'];

        cast_assoc_float($item, ['a', 'b']);

        $this->assertSame(['a' => 1.1, 'b' => 2.2, 'c' => '3.3'], $item);
    }

    public function test_cast_assoc_float_with_precision(): void
    {
        $item = ['a' => '1.111', 'b' => '2.222', 'c' => '3.333'];

        cast_assoc_float($item, ['a', 'b'], 2);

        $this->assertSame(['a' => 1.11, 'b' => 2.22, 'c' => '3.333'], $item);
    }

    public function test_cast_assoc_float_with_mode_round_half_up(): void
    {
        $item = ['a' => '1.5', 'b' => '2.5', 'c' => '3.5'];

        cast_assoc_float($item, ['a', 'b'], 0, PHP_ROUND_HALF_UP);

        $this->assertSame(['a' => 2.0, 'b' => 3.0, 'c' => '3.5'], $item);
    }

    public function test_cast_assoc_float_with_mode_round_half_down(): void
    {
        $item = ['a' => '1.5', 'b' => '2.5', 'c' => '3.5'];

        cast_assoc_float($item, ['a', 'b'], 0, PHP_ROUND_HALF_DOWN);

        $this->assertSame(['a' => 1.0, 'b' => 2.0, 'c' => '3.5'], $item);
    }

    public function test_cast_assoc_float_with_model_round_half_even(): void
    {
        $item = ['a' => '1.5', 'b' => '2.5', 'c' => '3.5'];

        cast_assoc_float($item, ['a', 'b'], 0, PHP_ROUND_HALF_EVEN);

        $this->assertSame(['a' => 2.0, 'b' => 2.0, 'c' => '3.5'], $item);
    }

    public function test_cast_assoc_float_with_model_round_half_odd(): void
    {
        $item = ['a' => '1.5', 'b' => '2.5', 'c' => '3.5'];

        cast_assoc_float($item, ['a', 'b'], 0, PHP_ROUND_HALF_ODD);

        $this->assertSame(['a' => 1.0, 'b' => 3.0, 'c' => '3.5'], $item);
    }

    public function test_cast_assoc_numeric(): void
    {
        $item = ['a' => '1.1', 'b' => '2.2', 'c' => '3.3'];

        cast_assoc_numeric($item, ['a', 'b']);

        $this->assertSame(['a' => 1.1, 'b' => 2.2, 'c' => '3.3'], $item);
    }
}
