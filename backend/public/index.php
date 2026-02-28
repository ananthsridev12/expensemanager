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
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    exit;
}

header('Access-Control-Allow-Origin: *');

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (!hash_equals((string) $config['app']['api_key'], (string) $apiKey)) {
    JsonResponse::error('Unauthorized', 401);
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$route = normalizeRoute($path);
$method = strtoupper($_SERVER['REQUEST_METHOD']);

$input = readJsonBody();

try {
    if ($route === '/api/health' && $method === 'GET') {
        JsonResponse::ok(['status' => 'healthy']);
    }

    if ($route === '/api/account-types' && $method === 'GET') {
        $rows = $pdo->query('SELECT id, code, normal_side FROM account_types ORDER BY id')->fetchAll();
        JsonResponse::ok(['account_types' => $rows]);
    }

    if ($route === '/api/accounts' && $method === 'GET') {
        $userId = requireUserId($_GET);
        $stmt = $pdo->prepare(
            'SELECT a.id, a.name, a.code, a.currency, a.parent_account_id, a.is_active,
                    at.id AS account_type_id, at.code AS account_type,
                    COALESCE(b.net_debit_balance, 0) AS net_debit_balance
             FROM accounts a
             JOIN account_types at ON at.id = a.account_type_id
             LEFT JOIN account_balances b ON b.account_id = a.id AND b.user_id = a.user_id
             WHERE a.user_id = :user_id
             ORDER BY a.name'
        );
        $stmt->execute(['user_id' => $userId]);
        JsonResponse::ok(['accounts' => $stmt->fetchAll()]);
    }

    if ($route === '/api/accounts/create' && $method === 'POST') {
        $userId = requireUserId($input);
        requireFields($input, ['account_type_id', 'name']);

        $stmt = $pdo->prepare(
            'INSERT INTO accounts (user_id, account_type_id, name, code, currency, parent_account_id, metadata_json)
             VALUES (:user_id, :account_type_id, :name, :code, :currency, :parent_account_id, :metadata_json)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'account_type_id' => (int) $input['account_type_id'],
            'name' => trim((string) $input['name']),
            'code' => nullableString($input['code'] ?? null),
            'currency' => strtoupper((string) ($input['currency'] ?? 'INR')),
            'parent_account_id' => nullableInt($input['parent_account_id'] ?? null),
            'metadata_json' => isset($input['metadata']) ? json_encode($input['metadata']) : null,
        ]);

        JsonResponse::ok(['account_id' => (int) $pdo->lastInsertId()]);
    }

    if ($route === '/api/accounts/update' && $method === 'POST') {
        $userId = requireUserId($input);
        requireFields($input, ['id', 'name', 'account_type_id']);

        $stmt = $pdo->prepare(
            'UPDATE accounts
             SET account_type_id = :account_type_id,
                 name = :name,
                 code = :code,
                 currency = :currency,
                 parent_account_id = :parent_account_id,
                 is_active = :is_active,
                 metadata_json = :metadata_json
             WHERE id = :id AND user_id = :user_id'
        );

        $stmt->execute([
            'id' => (int) $input['id'],
            'user_id' => $userId,
            'account_type_id' => (int) $input['account_type_id'],
            'name' => trim((string) $input['name']),
            'code' => nullableString($input['code'] ?? null),
            'currency' => strtoupper((string) ($input['currency'] ?? 'INR')),
            'parent_account_id' => nullableInt($input['parent_account_id'] ?? null),
            'is_active' => isset($input['is_active']) ? ((int) ((bool) $input['is_active'])) : 1,
            'metadata_json' => isset($input['metadata']) ? json_encode($input['metadata']) : null,
        ]);

        JsonResponse::ok(['updated' => $stmt->rowCount() > 0]);
    }

    if ($route === '/api/categories' && $method === 'GET') {
        $userId = requireUserId($_GET);
        $stmt = $pdo->prepare(
            'SELECT id, kind, name, parent_category_id, is_active
             FROM categories
             WHERE user_id = :user_id
             ORDER BY kind, name'
        );
        $stmt->execute(['user_id' => $userId]);
        JsonResponse::ok(['categories' => $stmt->fetchAll()]);
    }

    if ($route === '/api/categories/create' && $method === 'POST') {
        $userId = requireUserId($input);
        requireFields($input, ['kind', 'name']);

        $stmt = $pdo->prepare(
            'INSERT INTO categories (user_id, kind, name, parent_category_id)
             VALUES (:user_id, :kind, :name, :parent_category_id)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'kind' => strtoupper((string) $input['kind']),
            'name' => trim((string) $input['name']),
            'parent_category_id' => nullableInt($input['parent_category_id'] ?? null),
        ]);

        JsonResponse::ok(['category_id' => (int) $pdo->lastInsertId()]);
    }

    if ($route === '/api/categories/update' && $method === 'POST') {
        $userId = requireUserId($input);
        requireFields($input, ['id', 'kind', 'name']);

        $stmt = $pdo->prepare(
            'UPDATE categories
             SET kind = :kind,
                 name = :name,
                 parent_category_id = :parent_category_id,
                 is_active = :is_active
             WHERE id = :id AND user_id = :user_id'
        );

        $stmt->execute([
            'id' => (int) $input['id'],
            'user_id' => $userId,
            'kind' => strtoupper((string) $input['kind']),
            'name' => trim((string) $input['name']),
            'parent_category_id' => nullableInt($input['parent_category_id'] ?? null),
            'is_active' => isset($input['is_active']) ? ((int) ((bool) $input['is_active'])) : 1,
        ]);

        JsonResponse::ok(['updated' => $stmt->rowCount() > 0]);
    }

    if ($route === '/api/credit-cards' && $method === 'GET') {
        $userId = requireUserId($_GET);

        $stmt = $pdo->prepare(
            'SELECT c.*, COALESCE(b.net_debit_balance, 0) AS principal_balance,
                    GREATEST(0, c.limit_amount - ABS(COALESCE(b.net_debit_balance, 0))) AS available_limit
             FROM credit_cards c
             LEFT JOIN account_balances b ON b.user_id = c.user_id AND b.account_id = c.principal_account_id
             WHERE c.user_id = :user_id
             ORDER BY c.name'
        );
        $stmt->execute(['user_id' => $userId]);

        JsonResponse::ok(['credit_cards' => $stmt->fetchAll()]);
    }

    if ($route === '/api/credit-cards/create' && $method === 'POST') {
        $userId = requireUserId($input);
        requireFields($input, ['name', 'limit_amount', 'billing_day', 'due_day', 'principal_account_id', 'interest_expense_account_id', 'gst_expense_account_id']);

        $stmt = $pdo->prepare(
            'INSERT INTO credit_cards
                (user_id, name, limit_amount, billing_day, due_day, principal_account_id, interest_expense_account_id, gst_expense_account_id, fee_expense_account_id)
             VALUES
                (:user_id, :name, :limit_amount, :billing_day, :due_day, :principal_account_id, :interest_expense_account_id, :gst_expense_account_id, :fee_expense_account_id)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'name' => trim((string) $input['name']),
            'limit_amount' => round((float) $input['limit_amount'], 2),
            'billing_day' => (int) $input['billing_day'],
            'due_day' => (int) $input['due_day'],
            'principal_account_id' => (int) $input['principal_account_id'],
            'interest_expense_account_id' => (int) $input['interest_expense_account_id'],
            'gst_expense_account_id' => (int) $input['gst_expense_account_id'],
            'fee_expense_account_id' => nullableInt($input['fee_expense_account_id'] ?? null),
        ]);

        JsonResponse::ok(['credit_card_id' => (int) $pdo->lastInsertId()]);
    }

    if ($route === '/api/loans' && $method === 'GET') {
        $userId = requireUserId($_GET);

        $stmt = $pdo->prepare(
            'SELECT l.*, COALESCE(b.net_debit_balance, 0) AS principal_balance
             FROM loans l
             LEFT JOIN account_balances b ON b.user_id = l.user_id AND b.account_id = l.principal_account_id
             WHERE l.user_id = :user_id
             ORDER BY l.name'
        );
        $stmt->execute(['user_id' => $userId]);

        JsonResponse::ok(['loans' => $stmt->fetchAll()]);
    }

    if ($route === '/api/loans/create' && $method === 'POST') {
        $userId = requireUserId($input);
        requireFields($input, ['name', 'principal_account_id', 'interest_expense_account_id']);

        $stmt = $pdo->prepare(
            'INSERT INTO loans
             (user_id, name, lender, principal_account_id, interest_expense_account_id, charges_expense_account_id, sanction_amount, start_date, end_date, interest_rate_annual)
             VALUES
             (:user_id, :name, :lender, :principal_account_id, :interest_expense_account_id, :charges_expense_account_id, :sanction_amount, :start_date, :end_date, :interest_rate_annual)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'name' => trim((string) $input['name']),
            'lender' => nullableString($input['lender'] ?? null),
            'principal_account_id' => (int) $input['principal_account_id'],
            'interest_expense_account_id' => (int) $input['interest_expense_account_id'],
            'charges_expense_account_id' => nullableInt($input['charges_expense_account_id'] ?? null),
            'sanction_amount' => isset($input['sanction_amount']) ? round((float) $input['sanction_amount'], 2) : null,
            'start_date' => nullableString($input['start_date'] ?? null),
            'end_date' => nullableString($input['end_date'] ?? null),
            'interest_rate_annual' => isset($input['interest_rate_annual']) ? round((float) $input['interest_rate_annual'], 4) : null,
        ]);

        JsonResponse::ok(['loan_id' => (int) $pdo->lastInsertId()]);
    }

    if ($route === '/api/investments' && $method === 'GET') {
        $userId = requireUserId($_GET);

        $stmt = $pdo->prepare(
            'SELECT i.*, COALESCE(b.net_debit_balance, 0) AS current_book_balance
             FROM investments i
             LEFT JOIN account_balances b ON b.user_id = i.user_id AND b.account_id = i.asset_account_id
             WHERE i.user_id = :user_id
             ORDER BY i.name'
        );
        $stmt->execute(['user_id' => $userId]);

        JsonResponse::ok(['investments' => $stmt->fetchAll()]);
    }

    if ($route === '/api/investments/create' && $method === 'POST') {
        $userId = requireUserId($input);
        requireFields($input, ['name', 'instrument_type', 'asset_account_id']);

        $stmt = $pdo->prepare(
            'INSERT INTO investments (user_id, name, instrument_type, asset_account_id, income_account_id)
             VALUES (:user_id, :name, :instrument_type, :asset_account_id, :income_account_id)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'name' => trim((string) $input['name']),
            'instrument_type' => trim((string) $input['instrument_type']),
            'asset_account_id' => (int) $input['asset_account_id'],
            'income_account_id' => nullableInt($input['income_account_id'] ?? null),
        ]);

        JsonResponse::ok(['investment_id' => (int) $pdo->lastInsertId()]);
    }

    if ($route === '/api/emis' && $method === 'GET') {
        $userId = requireUserId($_GET);
        $status = isset($_GET['status']) ? strtoupper((string) $_GET['status']) : null;

        $sql = 'SELECT e.*,
                       l.name AS loan_name,
                       c.name AS credit_card_name
                FROM emis e
                LEFT JOIN loans l ON l.id = e.loan_id
                LEFT JOIN credit_cards c ON c.id = e.credit_card_id
                WHERE e.user_id = :user_id';

        if ($status !== null && in_array($status, ['PENDING', 'PAID', 'SKIPPED'], true)) {
            $sql .= ' AND e.status = :status';
        }
        $sql .= ' ORDER BY e.due_date, e.id';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        if ($status !== null && in_array($status, ['PENDING', 'PAID', 'SKIPPED'], true)) {
            $stmt->bindValue(':status', $status);
        }
        $stmt->execute();

        JsonResponse::ok(['emis' => $stmt->fetchAll()]);
    }

    if ($route === '/api/emis/create' && $method === 'POST') {
        $userId = requireUserId($input);
        requireFields($input, ['due_date', 'total_amount']);

        $stmt = $pdo->prepare(
            'INSERT INTO emis
             (user_id, loan_id, credit_card_id, due_date, principal_amount, interest_amount, gst_amount, fees_amount, total_amount, status)
             VALUES
             (:user_id, :loan_id, :credit_card_id, :due_date, :principal_amount, :interest_amount, :gst_amount, :fees_amount, :total_amount, :status)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'loan_id' => nullableInt($input['loan_id'] ?? null),
            'credit_card_id' => nullableInt($input['credit_card_id'] ?? null),
            'due_date' => (string) $input['due_date'],
            'principal_amount' => round((float) ($input['principal_amount'] ?? 0), 2),
            'interest_amount' => round((float) ($input['interest_amount'] ?? 0), 2),
            'gst_amount' => round((float) ($input['gst_amount'] ?? 0), 2),
            'fees_amount' => round((float) ($input['fees_amount'] ?? 0), 2),
            'total_amount' => round((float) $input['total_amount'], 2),
            'status' => strtoupper((string) ($input['status'] ?? 'PENDING')),
        ]);

        JsonResponse::ok(['emi_id' => (int) $pdo->lastInsertId()]);
    }

    if ($route === '/api/emis/mark-paid' && $method === 'POST') {
        $userId = requireUserId($input);
        requireFields($input, ['emi_id', 'payment_transaction_id']);

        $stmt = $pdo->prepare(
            'UPDATE emis
             SET status = \'PAID\', payment_transaction_id = :payment_transaction_id
             WHERE id = :emi_id AND user_id = :user_id'
        );
        $stmt->execute([
            'emi_id' => (int) $input['emi_id'],
            'user_id' => $userId,
            'payment_transaction_id' => (int) $input['payment_transaction_id'],
        ]);

        JsonResponse::ok(['updated' => $stmt->rowCount() > 0]);
    }

    if ($route === '/api/transactions' && $method === 'GET') {
        $userId = requireUserId($_GET);
        $limit = max(1, min(200, (int) ($_GET['limit'] ?? 50)));

        $stmt = $pdo->prepare(
            'SELECT id, txn_type, txn_date, description, status, created_at, updated_at
             FROM transactions
             WHERE user_id = :user_id
             ORDER BY txn_date DESC, id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        JsonResponse::ok(['transactions' => $stmt->fetchAll()]);
    }

    if ($route === '/api/transactions/view' && $method === 'GET') {
        $userId = requireUserId($_GET);
        $transactionId = (int) ($_GET['id'] ?? 0);
        if ($transactionId <= 0) {
            JsonResponse::error('id is required', 422);
        }

        $txStmt = $pdo->prepare(
            'SELECT id, txn_type, txn_date, description, status, external_ref, created_at
             FROM transactions
             WHERE id = :id AND user_id = :user_id'
        );
        $txStmt->execute(['id' => $transactionId, 'user_id' => $userId]);
        $transaction = $txStmt->fetch();

        if (!$transaction) {
            JsonResponse::error('Transaction not found', 404);
        }

        $entryStmt = $pdo->prepare(
            'SELECT je.id, je.account_id, a.name AS account_name, je.category_id, c.name AS category_name,
                    je.side, je.amount, je.note
             FROM journal_entries je
             JOIN accounts a ON a.id = je.account_id
             LEFT JOIN categories c ON c.id = je.category_id
             WHERE je.transaction_id = :transaction_id AND je.user_id = :user_id
             ORDER BY je.id'
        );
        $entryStmt->execute(['transaction_id' => $transactionId, 'user_id' => $userId]);

        JsonResponse::ok([
            'transaction' => $transaction,
            'entries' => $entryStmt->fetchAll(),
        ]);
    }

    if ($route === '/api/transactions/create' && $method === 'POST') {
        $userId = requireUserId($input);
        $transactionId = $posting->createTransaction($userId, $input);
        JsonResponse::ok(['transaction_id' => $transactionId]);
    }

    if ($route === '/api/reports/monthly' && $method === 'GET') {
        $userId = requireUserId($_GET);
        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('m'));

        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-d', strtotime($start . ' +1 month'));

        $summaryStmt = $pdo->prepare(
            'SELECT
                SUM(CASE WHEN at.code = "INCOME" AND je.side = "CREDIT" THEN je.amount ELSE 0 END) AS total_income,
                SUM(CASE WHEN at.code = "EXPENSE" AND je.side = "DEBIT" THEN je.amount ELSE 0 END) AS total_expense
             FROM journal_entries je
             JOIN transactions t ON t.id = je.transaction_id
             JOIN accounts a ON a.id = je.account_id
             JOIN account_types at ON at.id = a.account_type_id
             WHERE je.user_id = :user_id
               AND t.txn_date >= :start
               AND t.txn_date < :end
               AND t.status = \'POSTED\''
        );
        $summaryStmt->execute([
            'user_id' => $userId,
            'start' => $start,
            'end' => $end,
        ]);

        $categoryStmt = $pdo->prepare(
            'SELECT c.id AS category_id, c.name AS category_name, SUM(je.amount) AS total
             FROM journal_entries je
             JOIN transactions t ON t.id = je.transaction_id
             JOIN accounts a ON a.id = je.account_id
             JOIN account_types at ON at.id = a.account_type_id
             LEFT JOIN categories c ON c.id = je.category_id
             WHERE je.user_id = :user_id
               AND t.txn_date >= :start
               AND t.txn_date < :end
               AND t.status = \'POSTED\'
               AND at.code = \'EXPENSE\'
               AND je.side = \'DEBIT\'
             GROUP BY c.id, c.name
             ORDER BY total DESC'
        );
        $categoryStmt->execute([
            'user_id' => $userId,
            'start' => $start,
            'end' => $end,
        ]);

        JsonResponse::ok([
            'period' => ['year' => $year, 'month' => $month, 'start' => $start, 'end_exclusive' => $end],
            'summary' => $summaryStmt->fetch(),
            'expense_by_category' => $categoryStmt->fetchAll(),
        ]);
    }

    if ($route === '/api/dashboard/summary' && $method === 'GET') {
        $userId = requireUserId($_GET);

        $balancesStmt = $pdo->prepare(
            'SELECT at.code AS account_type,
                    SUM(COALESCE(b.net_debit_balance, 0)) AS total
             FROM accounts a
             JOIN account_types at ON at.id = a.account_type_id
             LEFT JOIN account_balances b ON b.user_id = a.user_id AND b.account_id = a.id
             WHERE a.user_id = :user_id AND a.is_active = 1
             GROUP BY at.code'
        );
        $balancesStmt->execute(['user_id' => $userId]);

        $duesStmt = $pdo->prepare(
            'SELECT id, due_date, total_amount, status, loan_id, credit_card_id
             FROM emis
             WHERE user_id = :user_id
               AND status = \'PENDING\'
               AND due_date <= DATE_ADD(CURDATE(), INTERVAL 15 DAY)
             ORDER BY due_date, id'
        );
        $duesStmt->execute(['user_id' => $userId]);

        JsonResponse::ok([
            'balances_by_type' => $balancesStmt->fetchAll(),
            'upcoming_emi_dues' => $duesStmt->fetchAll(),
        ]);
    }

    JsonResponse::error('Route not found', 404);
} catch (Throwable $e) {
    JsonResponse::error($e->getMessage(), 422);
}

function normalizeRoute(string $path): string
{
    if (preg_match('#/index\.php(/.*)$#', $path, $matches) === 1) {
        return $matches[1];
    }

    return $path;
}

function readJsonBody(): array
{
    $raw = (string) file_get_contents('php://input');
    if ($raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        JsonResponse::error('Invalid JSON payload', 422);
    }

    return $decoded;
}

function requireUserId(array $source): int
{
    $userId = (int) ($source['user_id'] ?? 0);
    if ($userId <= 0) {
        JsonResponse::error('user_id is required', 422);
    }

    return $userId;
}

function requireFields(array $source, array $fields): void
{
    foreach ($fields as $field) {
        if (!array_key_exists($field, $source) || $source[$field] === null || $source[$field] === '') {
            JsonResponse::error($field . ' is required', 422);
        }
    }
}

function nullableInt(mixed $value): ?int
{
    if ($value === null || $value === '') {
        return null;
    }

    return (int) $value;
}

function nullableString(mixed $value): ?string
{
    if ($value === null) {
        return null;
    }

    $text = trim((string) $value);
    if ($text === '') {
        return null;
    }

    return $text;
}
