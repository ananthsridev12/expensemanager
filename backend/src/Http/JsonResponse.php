<?php

declare(strict_types=1);

namespace App\Http;

final class JsonResponse
{
    public static function send(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function ok(array $data = []): void
    {
        self::send(['ok' => true, 'data' => $data], 200);
    }

    public static function error(string $message, int $status = 400, array $meta = []): void
    {
        self::send(['ok' => false, 'error' => $message, 'meta' => $meta], $status);
    }
}
