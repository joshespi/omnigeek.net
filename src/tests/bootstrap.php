<?php

// The app container runs with APP_ENV=local injected as an OS env var (root .env via
// docker-compose). Laravel resolves its environment from that OS value at bootstrap,
// before PHPUnit's <env> overrides apply, so tests would otherwise run as "local" and
// Livewire's test macros (assertSeeVolt etc.) never register. Force testing here, in all
// three stores, before the framework boots.
putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

require __DIR__.'/../vendor/autoload.php';
