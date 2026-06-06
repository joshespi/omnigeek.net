<?php

// The app container runs with APP_ENV=local + mysql DB creds injected as OS env vars
// (root .env via docker-compose). Laravel's Env::get() reads $_SERVER first, before
// PHPUnit's <env> overrides apply — and those overrides only touch $_ENV/putenv, never
// $_SERVER. So without this, tests boot as "local" against the live MariaDB and
// RefreshDatabase truncates the real database. Force testing values in all three stores.
$testEnv = [
    'APP_ENV' => 'testing',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'DB_HOST' => '',
    'DB_PORT' => '',
    'DB_USERNAME' => '',
    'DB_PASSWORD' => '',
    'DB_URL' => '',
];

foreach ($testEnv as $key => $value) {
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv("$key=$value");
}

require __DIR__.'/../vendor/autoload.php';
