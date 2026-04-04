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
    public function test_constructor(): void
    {
        $item = new Item(['a' => 'b']);

        $this->assertSame(['a' => 'b'], $item->toArray());
    }

    public function test_constructor_with_array_access(): void
    {
        $item = new Item(new \ArrayObject(['a' => 'b']));

        $this->assertSame(['a' => 'b'], $item->toArray());
    }

    public function test_constructor_with_null(): void
    {
        $item = new Item(null);

        $this->assertSame([], $item->toArray());
    }

    public function test_constructor_with_empty_string(): void
    {
        $item = new Item('');

        $this->assertSame([], $item->toArray());
    }

    public function test_constructor_with_std_class(): void
    {
        $item = new Item((object) ['a' => 'b']);

        $this->assertSame(['a' => 'b'], $item->toArray());
    }

    public function test_constructor_with_nested_array(): void
    {
        $item = new Item(['a' => ['b' => 'c']]);

        $this->assertSame(['a' => ['b' => 'c']], $item->toArray());
    }

    // Test para validar todos los metodos de la clase \Joalvm\Item.

    public function test_offset_exists(): void
    {
        $item = new Item(['a' => 'b']);

        $this->assertTrue($item->offsetExists('a'));
        $this->assertFalse($item->offsetExists('b'));
    }

    public function test_offset_get(): void
    {
        $item = new Item(['a' => 'b']);

        $this->assertSame('b', $item->offsetGet('a'));
        $this->assertNull($item->offsetGet('b'));
    }

    public function test_offset_set(): void
    {
        $item = new Item(['a' => 'b']);

        $item->offsetSet('a', 'c');
        $item->offsetSet('b', 'd');

        $this->assertSame('c', $item->offsetGet('a'));
        $this->assertSame('d', $item->offsetGet('b'));
    }

    public function test_offset_unset(): void
    {
        $item = new Item(['a' => 'b']);

        $item->offsetUnset('a');

        $this->assertNull($item->offsetGet('a'));
    }

    public function test_get(): void
    {
        $item = new Item(['a' => 'b']);

        $this->assertSame('b', $item->get('a'));
        $this->assertNull($item->get('b'));
    }

    public function test_get_with_nested_array(): void
    {
        $item = new Item(['a' => ['b' => 'c']]);

        $this->assertSame(['b' => 'c'], $item->get('a'));
        $this->assertSame('c', $item->get('a.b'));
        $this->assertNull($item->get('a.c'));
    }

    public function test_get_with_nested_array_and_default(): void
    {
        $item = new Item(['a' => ['b' => 'c']]);

        $this->assertSame('c', $item->get('a.b', 'd'));
        $this->assertSame('d', $item->get('a.c', 'd'));
    }

    public function test_get_with_nested_array_and_default_null(): void
    {
        $item = new Item(['a' => ['b' => 'c']]);

        $this->assertSame('c', $item->get('a.b'));
        $this->assertNull($item->get('a.c'));
    }

    public function test_set(): void
    {
        $item = new Item(['a' => 'b']);

        $item->set('a', 'c');
        $item->set('b', 'd');

        $this->assertSame('c', $item->get('a'));
        $this->assertSame('d', $item->get('b'));
    }

    public function test_set_with_nested_array(): void
    {
        $item = new Item(['a' => ['b' => 'c']]);

        $item->set('a.b', 'd');

        $this->assertSame('d', $item->get('a.b'));
    }

    public function test_set_with_nested_array_and_empty_array(): void
    {
        $item = new Item(['a' => []]);

        $item->set('a.b', 'd');

        $this->assertSame(['b' => 'd'], $item->get('a'));
    }

    public function test_set_with_nested_array_and_empty_array_and_empty_key(): void
    {
        $item = new Item(['a' => []]);

        $item->set('a', 'd');

        $this->assertSame('d', $item->get('a'));
    }

    public function test_count(): void
    {
        $item = new Item(['a' => 'b']);

        $this->assertSame(1, $item->count());
    }

    public function test_count_with_nested_array(): void
    {
        $item = new Item(['a' => ['b' => 'c']]);

        $this->assertSame(1, $item->count());
    }

    public function test_schematize(): void
    {
        $item = new Item(['a.b' => 'c']);

        $this->assertSame(['a' => ['b' => 'c']], $item->schematize()->toArray());
    }

    public function test_schematize_with_cast(): void
    {
        $item = new Item(['a.b' => 3.59]);

        $item->schematize(function (Item &$item) {
            $item->castFloatValues(['a.b']);
        });

        $this->assertSame(['a' => ['b' => 4.0]], $item->toArray());
    }
}
