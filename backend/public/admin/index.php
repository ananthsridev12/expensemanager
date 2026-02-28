<?php
require __DIR__ . '/_bootstrap.php';

$user = require_admin();
$userId = (int) $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'add_account') {
            $stmt = $pdo->prepare(
                'INSERT INTO accounts (user_id, account_type_id, name, code, currency, parent_account_id, metadata_json)
                 VALUES (:user_id, :account_type_id, :name, :code, :currency, :parent_account_id, :metadata_json)'
            );

            $stmt->execute([
                'user_id' => $userId,
                'account_type_id' => (int) $_POST['account_type_id'],
                'name' => trim((string) $_POST['name']),
                'code' => trim((string) ($_POST['code'] ?? '')) ?: null,
                'currency' => trim((string) ($_POST['currency'] ?? 'INR')),
                'parent_account_id' => !empty($_POST['parent_account_id']) ? (int) $_POST['parent_account_id'] : null,
                'metadata_json' => !empty($_POST['metadata_json']) ? (string) $_POST['metadata_json'] : null,
            ]);

            flash('success', 'Account created.');
        }

        if ($action === 'add_category') {
            $stmt = $pdo->prepare('INSERT INTO categories (user_id, kind, name, parent_category_id) VALUES (:user_id, :kind, :name, :parent_category_id)');
            $stmt->execute([
                'user_id' => $userId,
                'kind' => (string) $_POST['kind'],
                'name' => trim((string) $_POST['name']),
                'parent_category_id' => !empty($_POST['parent_category_id']) ? (int) $_POST['parent_category_id'] : null,
            ]);

            flash('success', 'Category created.');
        }

        if ($action === 'create_transaction') {
            $payload = json_decode((string) ($_POST['payload_json'] ?? ''), true);
            if (!is_array($payload)) {
                throw new RuntimeException('Payload JSON is invalid.');
            }

            $payload['user_id'] = $userId;
            $transactionId = $postingService->createTransaction($userId, $payload);
            flash('success', 'Transaction posted. ID: ' . $transactionId);
        }
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
    }

    header('Location: index.php');
    exit;
}

$flash = pull_flash();

$accountTypes = $pdo->query('SELECT id, code FROM account_types ORDER BY id')->fetchAll();
$accounts = $pdo->prepare(
    'SELECT a.id, a.name, at.code AS account_type, a.currency, COALESCE(b.net_debit_balance, 0) AS net_debit_balance
     FROM accounts a
     JOIN account_types at ON at.id = a.account_type_id
     LEFT JOIN account_balances b ON b.user_id = a.user_id AND b.account_id = a.id
     WHERE a.user_id = :user_id AND a.is_active = 1
     ORDER BY a.name'
);
$accounts->execute(['user_id' => $userId]);
$accounts = $accounts->fetchAll();

$categories = $pdo->prepare('SELECT id, kind, name FROM categories WHERE user_id = :user_id AND is_active = 1 ORDER BY kind, name');
$categories->execute(['user_id' => $userId]);
$categories = $categories->fetchAll();

$transactions = $pdo->prepare('SELECT id, txn_type, txn_date, description, created_at FROM transactions WHERE user_id = :user_id ORDER BY id DESC LIMIT 20');
$transactions->execute(['user_id' => $userId]);
$transactions = $transactions->fetchAll();

$samplePayload = [
    'txn_type' => 'card_emi_payment',
    'txn_date' => date('Y-m-d'),
    'description' => 'Card EMI payment',
    'principal_amount' => 3000,
    'interest_amount' => 250,
    'gst_amount' => 45,
    'fees_amount' => 0,
    'total_amount' => 3295,
    'principal_liability_account_id' => 10,
    'interest_expense_account_id' => 11,
    'gst_expense_account_id' => 12,
    'fees_expense_account_id' => 13,
    'payment_account_id' => 2,
];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Expense Manager Admin</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f7f7f7; }
    .top { display: flex; justify-content: space-between; align-items: center; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .card { background: #fff; border: 1px solid #ddd; padding: 14px; border-radius: 8px; }
    input, select, textarea { width: 100%; margin: 6px 0 10px 0; padding: 8px; box-sizing: border-box; }
    button { padding: 8px 12px; }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th, td { border-bottom: 1px solid #eee; padding: 6px; text-align: left; }
    .msg { padding: 10px; margin: 12px 0; }
    .success { background: #e8ffee; }
    .error { background: #ffeaea; }
  </style>
</head>
<body>
  <div class="top">
    <h1>Expense Manager Admin</h1>
    <div>
      Logged in as <?php echo h($user['email']); ?> | <a href="logout.php">Logout</a>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="msg <?php echo h($flash['type']); ?>"><?php echo h($flash['message']); ?></div>
  <?php endif; ?>

  <div class="grid">
    <div class="card">
      <h2>Add Account</h2>
      <form method="post">
        <input type="hidden" name="action" value="add_account" />
        <label>Name</label>
        <input name="name" required />
        <label>Type</label>
        <select name="account_type_id" required>
          <?php foreach ($accountTypes as $type): ?>
            <option value="<?php echo (int) $type['id']; ?>"><?php echo h($type['code']); ?></option>
          <?php endforeach; ?>
        </select>
        <label>Code (optional)</label>
        <input name="code" />
        <label>Currency</label>
        <input name="currency" value="INR" />
        <label>Parent Account ID (optional)</label>
        <input name="parent_account_id" type="number" />
        <label>Metadata JSON (optional)</label>
        <textarea name="metadata_json" rows="3"></textarea>
        <button type="submit">Create Account</button>
      </form>
    </div>

    <div class="card">
      <h2>Add Category</h2>
      <form method="post">
        <input type="hidden" name="action" value="add_category" />
        <label>Name</label>
        <input name="name" required />
        <label>Kind</label>
        <select name="kind" required>
          <option>INCOME</option>
          <option>EXPENSE</option>
          <option>TRANSFER</option>
          <option>INVESTMENT</option>
          <option>LOAN</option>
        </select>
        <label>Parent Category ID (optional)</label>
        <input name="parent_category_id" type="number" />
        <button type="submit">Create Category</button>
      </form>
    </div>
  </div>

  <div class="card" style="margin-top:16px;">
    <h2>Post Transaction (JSON)</h2>
    <p>Use transaction template fields. This uses the same posting engine as API and validates balancing rules.</p>
    <form method="post">
      <input type="hidden" name="action" value="create_transaction" />
      <textarea name="payload_json" rows="14" required><?php echo h(json_encode($samplePayload, JSON_PRETTY_PRINT)); ?></textarea>
      <button type="submit">Post Transaction</button>
    </form>
  </div>

  <div class="grid" style="margin-top:16px;">
    <div class="card">
      <h2>Accounts</h2>
      <table>
        <thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Balance (debit sign)</th></tr></thead>
        <tbody>
          <?php foreach ($accounts as $row): ?>
            <tr>
              <td><?php echo (int) $row['id']; ?></td>
              <td><?php echo h($row['name']); ?></td>
              <td><?php echo h($row['account_type']); ?></td>
              <td><?php echo number_format((float) $row['net_debit_balance'], 2); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="card">
      <h2>Categories</h2>
      <table>
        <thead><tr><th>ID</th><th>Kind</th><th>Name</th></tr></thead>
        <tbody>
          <?php foreach ($categories as $row): ?>
            <tr>
              <td><?php echo (int) $row['id']; ?></td>
              <td><?php echo h($row['kind']); ?></td>
              <td><?php echo h($row['name']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card" style="margin-top:16px;">
    <h2>Recent Transactions</h2>
    <table>
      <thead><tr><th>ID</th><th>Date</th><th>Type</th><th>Description</th><th>Created</th></tr></thead>
      <tbody>
        <?php foreach ($transactions as $row): ?>
          <tr>
            <td><?php echo (int) $row['id']; ?></td>
            <td><?php echo h($row['txn_date']); ?></td>
            <td><?php echo h($row['txn_type']); ?></td>
            <td><?php echo h((string) ($row['description'] ?? '')); ?></td>
            <td><?php echo h($row['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
