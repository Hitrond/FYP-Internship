<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

$configCache = dirname(__DIR__).'/bootstrap/cache/config.php';

if (! is_file($configCache)) {
    return;
}

$cachedConfig = require $configCache;
$defaultConnection = $cachedConfig['database']['default'] ?? null;
$database = $cachedConfig['database']['connections'][$defaultConnection]['database'] ?? null;
$environment = $cachedConfig['app']['env'] ?? null;

if ($environment !== 'testing' || $database !== 'testing') {
    throw new RuntimeException(sprintf(
        'Refusing to run tests with cached environment [%s] and database [%s]. Run "php artisan config:clear" first.',
        (string) $environment,
        (string) $database,
    ));
}
