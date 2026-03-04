<?php
$activeModule = 'investments';
$investments = $investments ?? [];
$transactions = $transactions ?? [];
$accounts = $accounts ?? [];
$summary = $summary ?? ['count' => 0];

include __DIR__ . '/../partials/nav.php';
?>
<main class="module-content">
    <header class="module-header">
        <h1>Investments</h1>
        <p>Track mutual funds, equities, FD/RD locks, and keep every transaction immutable.</p>
    </header>

    <section class="summary-cards">
        <article class="card">
            <h3>Investments</h3>
            <p><?= $summary['count'] ?></p>
        </article>
    </section>

    <section class="module-panel">
        <h2>New investment</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="investment">
            <label>
                Type
                <select name="type">
                    <option value="mutual_fund">Mutual Fund</option>
                    <option value="equity">Equity</option>
                    <option value="fd">FD</option>
                    <option value="rd">RD</option>
                    <option value="other">Other</option>
                </select>
            </label>
            <label>
                Name
                <input type="text" name="name" required>
            </label>
            <label>
                Notes
                <textarea name="notes" rows="2"></textarea>
            </label>
            <button type="submit">Create investment</button>
        </form>
    </section>

    <section class="module-panel">
        <h2>Investment transaction</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="investment_transaction">
            <label>
                Investment
                <select name="investment_id" required>
                    <?php foreach ($investments as $inv): ?>
                        <option value="<?= $inv['id'] ?>"><?= htmlspecialchars($inv['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Transaction type
                <select name="transaction_type">
                    <option value="buy">Buy</option>
                    <option value="sell">Sell</option>
                    <option value="dividend">Dividend</option>
                </select>
            </label>
            <label>
                Amount
                <input type="number" name="amount" step="0.01" required>
            </label>
            <label>
                Units
                <input type="number" name="units" step="0.0001">
            </label>
            <label>
                Date
                <input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required>
            </label>
            <label>
                Account
                <select name="account_id">
                    <option value="">None</option>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['bank_name'] . ' - ' . $account['account_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Notes
                <textarea name="notes" rows="2"></textarea>
            </label>
            <button type="submit">Save transaction</button>
        </form>
    </section>

    <section class="module-panel">
        <h2>Investment list</h2>
        <?php if (empty($investments)): ?>
            <p class="muted">No investment records yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($investments as $investment): ?>
                            <tr>
                                <td><?= htmlspecialchars($investment['name']) ?></td>
                                <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $investment['type']))) ?></td>
                                <td><?= htmlspecialchars($investment['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="module-panel">
        <h2>Recent investment transactions</h2>
        <?php if (empty($transactions)): ?>
            <p class="muted">No investment transactions yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Investment</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Account</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $txn): ?>
                            <tr>
                                <td><?= htmlspecialchars($txn['transaction_date']) ?></td>
                                <td><?= htmlspecialchars($txn['investment_name'] ?? '?') ?></td>
                                <td><?= htmlspecialchars(ucfirst($txn['transaction_type'])) ?></td>
                                <td><?= formatCurrency((float) $txn['amount']) ?></td>
                                <td><?= htmlspecialchars($txn['account_name'] ?? '?') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
