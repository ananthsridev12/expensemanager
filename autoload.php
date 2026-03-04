<?php

require_once __DIR__ . '/helpers.php';

spl_autoload_register(function (string $class): void {
    $prefixes = [
        'Config\\' => 'config/',
        'Models\\' => 'models/',
        'Controllers\\' => 'controllers/',
    ];

    foreach ($prefixes as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $relativeClass = substr($class, strlen($prefix));
            $path = __DIR__ . '/' . $dir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($path)) {
                require_once $path;
            }
            return;
        }
    }
});
