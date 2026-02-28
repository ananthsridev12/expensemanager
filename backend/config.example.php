<?php

declare(strict_types=1);

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'expense_manager',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => 'http://localhost/expense-manager/backend/public',
        'api_key' => 'change-this-to-a-long-random-string',
        'timezone' => 'Asia/Kolkata',
        'session_name' => 'expense_admin',
    ],
];
