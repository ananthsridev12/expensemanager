<?php

declare(strict_types=1);

use App\Database;
use App\Services\AuthService;
use App\Services\PostingService;

require dirname(__DIR__, 2) . '/bootstrap.php';

session_name($config['app']['session_name'] ?? 'expense_admin');
session_start();

$pdo = Database::connection($config);
$authService = new AuthService($pdo);
$postingService = new PostingService($pdo);

function admin_user(): ?array
{
    return $_SESSION['admin_user'] ?? null;
}

function require_admin(): array
{
    $user = admin_user();
    if ($user === null) {
        header('Location: login.php');
        exit;
    }

    return $user;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function pull_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
