<?php

require __DIR__ . './vendor/autoload.php';

use Joalvm\Utils\Builder;

// iniciamos laravel app
$app = new Illumi(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$builder = new Builder();

$builder->table('users as u');
