<?php

namespace Joalvm\Tests;

use Joalvm\Utils\Collection;
use Joalvm\Utils\Item;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class CollectionTest extends TestCase
{
    // Genera todos los test para la clase Joalvm\Utils\Collection.

    public function testConstructor(): void
    {
        $collection = new Collection(
            collect([(object) ['a.b' => 'c']]),
            ['a' => ['b' => 'c']]
        );

        $this->assertEquals([new Item(['a' => ['b' => 'c']])], $collection->toArray());
    }
}
