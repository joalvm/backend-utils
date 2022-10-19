<?php

declare(strict_types=1);

namespace Joalvm\Tests\Helpers;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ParsingTest extends TestCase
{
    public function testToStr(): void
    {
        $this->assertSame('string', to_str('  string  '));
    }

    public function testToInt(): void
    {
        $this->assertSame(1, to_int('1'));
    }

    public function testToFloat(): void
    {
        $this->assertSame(1.0, to_float('1'));
    }

    public function testToFloatWithPrecision(): void
    {
        $this->assertSame(1.1, to_float('1.09', 1));
        $this->assertSame(1.52, to_float('1.519', 2));
    }

    public function testToFloatWithPrecisionAndMode(): void
    {
        $this->assertSame(1.5, to_float('1.45', 1, PHP_ROUND_HALF_UP));
        $this->assertSame(1.4, to_float('1.45', 1, PHP_ROUND_HALF_DOWN));
    }

    public function testToNumeric(): void
    {
        $this->assertSame(1, to_numeric('1'));
        $this->assertSame(1.0, to_numeric('1.0'));
    }

    public function testToNumericWithPrecision(): void
    {
        $this->assertSame(1.1, to_numeric('1.09', 1));
        $this->assertSame(1.52, to_numeric('1.519', 2));
    }

    public function testToNumericWithPrecisionAndMode(): void
    {
        $this->assertSame(1.5, to_numeric('1.45', 1, PHP_ROUND_HALF_UP));
        $this->assertSame(1.4, to_numeric('1.45', 1, PHP_ROUND_HALF_DOWN));
    }

    public function testToBool(): void
    {
        $this->assertTrue(to_bool('true'));
        $this->assertFalse(to_bool('false'));
        $this->assertTrue(to_bool('1'));
        $this->assertFalse(to_bool('0'));
        $this->assertTrue(to_bool('yes'));
        $this->assertFalse(to_bool('no'));
        $this->assertTrue(to_bool('on'));
        $this->assertFalse(to_bool('off'));
        $this->assertTrue(to_bool('y'));
        $this->assertFalse(to_bool('n'));
        $this->assertTrue(to_bool('t'));
        $this->assertFalse(to_bool('f'));
    }

    public function testToList(): void
    {
        $this->assertSame(['a', 'b', 'c'], to_list('a,b,c'));
        $this->assertSame(['a', 'b', 'c'], to_list(['a', 'b', 'c']));
    }

    public function testToListWithEmptyValues(): void
    {
        $this->assertSame(['a', 'b', null, 'c'], to_list('a,b,,c', true));
        $this->assertSame(['a', 'b', 'c'], to_list('a,b,,c', false));
    }

    public function testToListInt(): void
    {
        $this->assertSame([1, 2, 3], to_list_int('1,2,3,a'));
        $this->assertSame([1, 2, 3], to_list_int([1, 2, 3, 'a']));
    }

    public function testToListIntWithEmptyValues(): void
    {
        $this->assertSame([1, 2, null, 3], to_list_int('1,2,,3,a', true));
        $this->assertSame([1, 2, 3], to_list_int('1,2,,3,a', false));
    }

    public function testToListFloat(): void
    {
        $this->assertSame([1.0, 2.0, 3.0], to_list_float('1.0,2.0,3.0,a'));
        $this->assertSame([1.0, 2.0, 3.0], to_list_float([1.0, 2.0, 3.0, 'a']));
    }

    public function testToListFloatWithEmptyValues(): void
    {
        $this->assertSame([1.0, 2.0, null, 3.0], to_list_float('1.0,2,,3.0,a', true));
        $this->assertSame([1.0, 2.0, 3.0], to_list_float('1.0,2,,3.0,a', false));
    }

    public function testToListNumeric(): void
    {
        $this->assertSame([1, 2, 3, 4.0], to_list_numeric('1,2,3,4.0,a'));
        $this->assertSame([1, 2, 3, 4.0], to_list_numeric([1, 2, 3, '4.0', 'a']));
    }

    public function testToListNumericWithEmptyValues(): void
    {
        $this->assertSame([1, 2, null, 3, 4.0], to_list_numeric('1,2,,3,4.0,a', true));
        $this->assertSame([1, 2, 3, 4.0], to_list_numeric('1,2,,3,4.0,a', false));
    }

    public function testToListBool(): void
    {
        $this->assertSame([true, false, true, false], to_list_bool('true,0,yes,false'));
        $this->assertSame([true, false, true, false], to_list_bool([true, false, 'yes', 'false']));
    }

    public function testToListBoolWithEmptyValues(): void
    {
        $this->assertSame([true, false, null, true, false], to_list_bool('true,0,,yes,false', true));
        $this->assertSame([true, false, true, false], to_list_bool('true,0,,yes,false', false));
    }
}
