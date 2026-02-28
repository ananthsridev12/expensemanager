<?php

declare(strict_types=1);

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    throw new RuntimeException('Missing backend/config.php. Copy backend/config.example.php to backend/config.php and update values.');
}

$config = require $configPath;

date_default_timezone_set($config['app']['timezone'] ?? 'UTC');

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = __DIR__ . '/src/' . $relative . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
