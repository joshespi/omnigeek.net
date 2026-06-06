<?php

return [
    // The initial admin user, created on first seed (DatabaseSeeder -> InitialUserSeeder).
    // Set these in the deployment's root .env.
    'initial_user' => [
        'name' => env('INITIAL_USER_NAME', 'Admin'),
        'email' => env('INITIAL_USER_EMAIL', 'admin@omnigeek.test'),
        'password' => env('INITIAL_USER_PASSWORD', 'password'),
    ],
];
