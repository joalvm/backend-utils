<?php

use Joalvm\Utils\Schematic\Schema;

require __DIR__ . '/vendor/autoload.php';

// Define el esquema que será utilizado en una consulta select
// permitiendo estructurar los datos de la respuesta y a la vez castear los tipos de datos.
$personSchema = Schema::object(fn ($col) => [
    'id' => $col->int(),
    'name' => $col->string()->capitalize(),
    'age' => $col->int()->raw('age > ?', [18])
]);