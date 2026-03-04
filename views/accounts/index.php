<?php
$activeModule = 'accounts';
$accountSummary = $summary ?? ['count' => 0, 'total_balance' => 0.0];
$accounts = $accounts ?? [];

include __DIR__ . '/../partials/nav.php';
?>
<main class="module-content">
    <header class="module-header">
        <h1>Accounts</h1>
        <p>Ledger-driven overview of your bank accounts and linked wallets.</p>
    </header>

    <section class="summary-cards">
        <article class="card">
            <h3>Total accounts</h3>
            <p><?= number_format($accountSummary['count'], 0) ?></p>
        </article>
        <article class="card">
            <h3>Ledger balance</h3>
            <p>? <?= number_format($accountSummary['total_balance'], 2) ?></p>
        </article>
    </section>

    <section class="module-panel">
        <h2>Add a new account</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="account">
            <label>
                Bank name
                <input type="text" name="bank_name" required>
            </label>
            <label>
                Account name
                <input type="text" name="account_name" required>
            </label>
            <label>
                Account number
                <input type="text" name="account_number">
            </label>
            <label>
                IFSC
                <input type="text" name="ifsc">
            </label>
            <label>
                Opening balance
                <input type="number" name="opening_balance" step="0.01" min="0" value="0.00">
            </label>
            <button type="submit">Save account</button>
        </form>
    </section>

    <section class="module-panel">
        <h2>Existing accounts</h2>
        <?php if (count($accounts) === 0): ?>
            <p class="muted">No accounts added yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Bank</th>
                            <th>Name</th>
                            <th>Balance</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $account): ?>
                            <tr>
                                <td><?= htmlspecialchars($account['bank_name']) ?></td>
                                <td><?= htmlspecialchars($account['account_name']) ?></td>
                                <td>? <?= number_format($account['balance'], 2) ?></td>
                                <td><?= htmlspecialchars($account['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
