<?php

namespace Joalvm\Tests;

// Genera todos los test para la clase Joalvm\Item.

use Joalvm\Utils\Item;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ItemTest extends TestCase
{
    public function testConstructor(): void
    {
        $item = new Item(['a' => 'b']);

        $this->assertSame(['a' => 'b'], $item->toArray());
    }

    public function testConstructorWithArrayAccess(): void
    {
        $item = new Item(new \ArrayObject(['a' => 'b']));

        $this->assertSame(['a' => 'b'], $item->toArray());
    }

    public function testConstructorWithNull(): void
    {
        $item = new Item(null);

        $this->assertSame([], $item->toArray());
    }

    public function testConstructorWithEmptyString(): void
    {
        $item = new Item('');

        $this->assertSame([], $item->toArray());
    }

    public function testConstructorWithStdClass(): void
    {
        $item = new Item((object) ['a' => 'b']);

        $this->assertSame(['a' => 'b'], $item->toArray());
    }

    public function testConstructorWithNestedArray(): void
    {
        $item = new Item(['a' => ['b' => 'c']]);

        $this->assertSame(['a' => ['b' => 'c']], $item->toArray());
    }

    // Test para validar todos los metodos de la clase \Joalvm\Item.

    public function testOffsetExists(): void
    {
        $item = new Item(['a' => 'b']);

        $this->assertTrue($item->offsetExists('a'));
        $this->assertFalse($item->offsetExists('b'));
    }

    public function testOffsetGet(): void
    {
        $item = new Item(['a' => 'b']);

        $this->assertSame('b', $item->offsetGet('a'));
        $this->assertNull($item->offsetGet('b'));
    }

    public function testOffsetSet(): void
    {
        $item = new Item(['a' => 'b']);

        $item->offsetSet('a', 'c');
        $item->offsetSet('b', 'd');

        $this->assertSame('c', $item->offsetGet('a'));
        $this->assertSame('d', $item->offsetGet('b'));
    }

    public function testOffsetUnset(): void
    {
        $item = new Item(['a' => 'b']);

        $item->offsetUnset('a');

        $this->assertNull($item->offsetGet('a'));
    }

    public function testGet(): void
    {
        $item = new Item(['a' => 'b']);

        $this->assertSame('b', $item->get('a'));
        $this->assertNull($item->get('b'));
    }

    public function testGetWithNestedArray(): void
    {
        $item = new Item(['a' => ['b' => 'c']]);

        $this->assertSame(['b' => 'c'], $item->get('a'));
        $this->assertSame('c', $item->get('a.b'));
        $this->assertNull($item->get('a.c'));
    }

    public function testGetWithNestedArrayAndDefault(): void
    {
        $item = new Item(['a' => ['b' => 'c']]);

        $this->assertSame('c', $item->get('a.b', 'd'));
        $this->assertSame('d', $item->get('a.c', 'd'));
    }

    public function testGetWithNestedArrayAndDefaultNull(): void
    {
        $item = new Item(['a' => ['b' => 'c']]);

        $this->assertSame('c', $item->get('a.b'));
        $this->assertNull($item->get('a.c'));
    }

    public function testSet(): void
    {
        $item = new Item(['a' => 'b']);

        $item->set('a', 'c');
        $item->set('b', 'd');

        $this->assertSame('c', $item->get('a'));
        $this->assertSame('d', $item->get('b'));
    }

    public function testSetWithNestedArray(): void
    {
        $item = new Item(['a' => ['b' => 'c']]);

        $item->set('a.b', 'd');

        $this->assertSame('d', $item->get('a.b'));
    }

    public function testSetWithNestedArrayAndEmptyArray(): void
    {
        $item = new Item(['a' => []]);

        $item->set('a.b', 'd');

        $this->assertSame(['b' => 'd'], $item->get('a'));
    }

    public function testSetWithNestedArrayAndEmptyArrayAndEmptyKey(): void
    {
        $item = new Item(['a' => []]);

        $item->set('a', 'd');

        $this->assertSame('d', $item->get('a'));
    }

    public function testCount(): void
    {
        $item = new Item(['a' => 'b']);

        $this->assertSame(1, $item->count());
    }

    public function testCountWithNestedArray(): void
    {
        $item = new Item(['a' => ['b' => 'c']]);

        $this->assertSame(1, $item->count());
    }

    public function testSchematize(): void
    {
        $item = new Item(['a.b' => 'c']);

        $this->assertSame(['a' => ['b' => 'c']], $item->schematize()->toArray());
    }

    public function testSchematizeWithCast(): void
    {
        $item = new Item(['a.b' => 3.59]);

        $item->schematize(function (Item &$item) {
            $item->castFloatValues(['a.b']);
        });

        $this->assertSame(['a' => ['b' => 4.0]], $item->toArray());
    }
}
