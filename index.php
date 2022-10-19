<?php

use Joalvm\Utils\Schematic\Item;
use Joalvm\Utils\Schematic\Schema;
use Joalvm\Utils\Schematic\SchemaManager;

require __DIR__ . '/vendor/autoload.php';

$manager = new SchemaManager('pgsql');

$schema = new Schema([
    'id' => Item::int('p2.id'),
    'name' => Item::str(),
    'lastname' => Item::str(function () {
        // ...
    }),
    'age' => Item::int(),
    'is_older' => Item::bool('age > ?', [18]),
    'email' => Item::str(),
    'metadata' => Item::json('{"a":"b"}'),
    'user' => new Schema('us', [
        'id' => Item::int(),
        'password' => Item::str(),
        'is_active' => Item::bool(),
        'last_session' => new Schema('ls', [
            'id' => Item::int(),
            'ip' => Item::str(),
            'created_at' => Item::str(),
            'updated_at' => Item::str(),
            'active' => [
                'is' => Item::bool('us.is_active'),
            ],
        ]),
    ]),
    'user2:us2' => [
        'id',
        'password' => 'p2.current_password',
        'is_active',
    ],
]);

$schema->setTableAs('p');

$schema->setFilters([
    'id',
    'name',
    'user2.*',
    'user.last_session.id',
]);

$manager->setSchema($schema)->prepare();

dd($manager->columns());
