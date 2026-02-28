<?php

declare(strict_types=1);

use App\Database;
use App\Services\AuthService;
use App\Services\PostingService;

$bootstrapCandidates = [
    dirname(__DIR__, 2) . '/bootstrap.php',
    dirname(__DIR__) . '/private/bootstrap.php',
    dirname(__DIR__, 2) . '/private/bootstrap.php',
];

$bootstrapLoaded = false;
foreach ($bootstrapCandidates as $bootstrapPath) {
    if (file_exists($bootstrapPath)) {
        require $bootstrapPath;
        $bootstrapLoaded = true;
        break;
    }
}

if (!$bootstrapLoaded) {
    throw new RuntimeException('Unable to locate bootstrap.php');
}

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
