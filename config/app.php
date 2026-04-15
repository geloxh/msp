<?php
    return [
        'name' => $_ENV['APP_NAME'] ?? 'MSP',
        'key' => $_ENV['APP_KEY'] ?? '',
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => $_ENV['APP_DEBUG'] ?? false,
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
        'locale' => $_ENV['APP_LOCALE'] ?? 'en',
    ];
