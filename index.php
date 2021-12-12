<?php

require __DIR__ . '/vendor/autoload.php';

use Joalvm\Utils\Builder;
use Joalvm\Utils\Schema\Columns\IntegerType;
use Joalvm\Utils\Schema\Field;
use Joalvm\Utils\Schema\Schema;

$schema = new Schema('person', [
    'id' => new IntegerType(),
    'age' => Field::int()->sub(function (Builder $builder) {
        $builder->select('age')->from('personal')->where('ed', '>', 18);
    }),
    'height' => Field::int()->raw('count(age)'),
    'user' => Schema::block('u', [
        'id' => Field::int(),
        'session' => Schema::block('us', [
            'id' => Field::int(),
            'timestamp' => Field::int('timestamp_2'),
            'location' => [
                'id' => Field::int(),
            ],
        ]),
    ]),
]);

$schema->filters(['person.age', 'user.id']);

$schema->shake();

dd($schema->getColumns());
