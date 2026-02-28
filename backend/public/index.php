<?php

declare(strict_types=1);

use App\Database;
use App\Http\JsonResponse;
use App\Services\PostingService;

$bootstrapCandidates = [
    dirname(__DIR__) . '/bootstrap.php',
    __DIR__ . '/private/bootstrap.php',
    dirname(__DIR__) . '/private/bootstrap.php',
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

$pdo = Database::connection($config);
$posting = new PostingService($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, X-Api-Key');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    exit;
}

header('Access-Control-Allow-Origin: *');

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (!hash_equals((string) $config['app']['api_key'], (string) $apiKey)) {
    JsonResponse::error('Unauthorized', 401);
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

if (str_ends_with($path, '/api/health') && $method === 'GET') {
    JsonResponse::ok(['status' => 'healthy']);
}

if (str_ends_with($path, '/api/accounts') && $method === 'GET') {
    $userId = (int) ($_GET['user_id'] ?? 0);
    if ($userId <= 0) {
        JsonResponse::error('user_id is required', 422);
    }

    $stmt = $pdo->prepare(
        'SELECT a.id, a.name, a.code, a.currency, at.code AS account_type, COALESCE(b.net_debit_balance, 0) AS net_debit_balance
         FROM accounts a
         JOIN account_types at ON at.id = a.account_type_id
         LEFT JOIN account_balances b ON b.account_id = a.id AND b.user_id = a.user_id
         WHERE a.user_id = :user_id AND a.is_active = 1
         ORDER BY a.name'
    );
    $stmt->execute(['user_id' => $userId]);

    JsonResponse::ok(['accounts' => $stmt->fetchAll()]);
}

if (str_ends_with($path, '/api/categories') && $method === 'GET') {
    $userId = (int) ($_GET['user_id'] ?? 0);
    if ($userId <= 0) {
        JsonResponse::error('user_id is required', 422);
    }

    $stmt = $pdo->prepare('SELECT id, kind, name, parent_category_id FROM categories WHERE user_id = :user_id AND is_active = 1 ORDER BY kind, name');
    $stmt->execute(['user_id' => $userId]);

    JsonResponse::ok(['categories' => $stmt->fetchAll()]);
}

if (str_ends_with($path, '/api/transactions') && $method === 'GET') {
    $userId = (int) ($_GET['user_id'] ?? 0);
    $limit = max(1, min(100, (int) ($_GET['limit'] ?? 30)));

    if ($userId <= 0) {
        JsonResponse::error('user_id is required', 422);
    }

    $stmt = $pdo->prepare('SELECT id, txn_type, txn_date, description, status, created_at FROM transactions WHERE user_id = :user_id ORDER BY txn_date DESC, id DESC LIMIT :limit');
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    JsonResponse::ok(['transactions' => $stmt->fetchAll()]);
}

if (str_ends_with($path, '/api/transactions/create') && $method === 'POST') {
    $input = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($input)) {
        JsonResponse::error('Invalid JSON payload', 422);
    }

    $userId = (int) ($input['user_id'] ?? 0);
    if ($userId <= 0) {
        JsonResponse::error('user_id is required', 422);
    }

    try {
        $transactionId = $posting->createTransaction($userId, $input);
        JsonResponse::ok(['transaction_id' => $transactionId]);
    } catch (Throwable $e) {
        JsonResponse::error($e->getMessage(), 422);
    }
}

if (str_ends_with($path, '/api/accounts/create') && $method === 'POST') {
    $input = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($input)) {
        JsonResponse::error('Invalid JSON payload', 422);
    }

    $required = ['user_id', 'account_type_id', 'name'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || $input[$field] === '') {
            JsonResponse::error("{$field} is required", 422);
        }
    }

    $stmt = $pdo->prepare(
        'INSERT INTO accounts (user_id, account_type_id, name, code, currency, parent_account_id, metadata_json)
         VALUES (:user_id, :account_type_id, :name, :code, :currency, :parent_account_id, :metadata_json)'
    );

    $stmt->execute([
        'user_id' => (int) $input['user_id'],
        'account_type_id' => (int) $input['account_type_id'],
        'name' => (string) $input['name'],
        'code' => $input['code'] ?? null,
        'currency' => $input['currency'] ?? 'INR',
        'parent_account_id' => isset($input['parent_account_id']) ? (int) $input['parent_account_id'] : null,
        'metadata_json' => isset($input['metadata']) ? json_encode($input['metadata']) : null,
    ]);

    JsonResponse::ok(['account_id' => (int) $pdo->lastInsertId()]);
}

if (str_ends_with($path, '/api/categories/create') && $method === 'POST') {
    $input = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($input)) {
        JsonResponse::error('Invalid JSON payload', 422);
    }

    $required = ['user_id', 'kind', 'name'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || $input[$field] === '') {
            JsonResponse::error("{$field} is required", 422);
        }
    }

    $stmt = $pdo->prepare('INSERT INTO categories (user_id, kind, name, parent_category_id) VALUES (:user_id, :kind, :name, :parent_category_id)');
    $stmt->execute([
        'user_id' => (int) $input['user_id'],
        'kind' => (string) $input['kind'],
        'name' => (string) $input['name'],
        'parent_category_id' => isset($input['parent_category_id']) ? (int) $input['parent_category_id'] : null,
    ]);

    JsonResponse::ok(['category_id' => (int) $pdo->lastInsertId()]);
}

JsonResponse::error('Route not found', 404);
