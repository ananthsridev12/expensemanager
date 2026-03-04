<?php
$activeModule = 'dashboard';
$summary = $summary ?? ['accounts' => ['count' => 0, 'total_balance' => 0], 'categories' => 0, 'transactions' => 0, 'reminders' => 0, 'loans' => ['count' => 0, 'principal' => 0], 'credit_cards' => ['count' => 0, 'total_limit' => 0, 'total_outstanding' => 0], 'lending' => ['count' => 0, 'outstanding' => 0], 'investments' => ['count' => 0], 'rentals' => ['contracts' => 0]];
$recentTransactions = $recentTransactions ?? [];
$upcomingReminders = $upcomingReminders ?? [];
$upcomingEmis = $upcomingEmis ?? [];

include __DIR__ . '/../partials/nav.php';
?>
<main class="module-content">
    <header class="module-header">
        <h1>Personal Finance Manager</h1>
        <p>Ledger-driven system ready to grow with bank accounts, loans, investments, and rental income.</p>
    </header>

    <section class="summary-cards">
        <article class="card">
            <h3>Bank balance</h3>
            <p><?= formatCurrency($summary['accounts']['total_balance']) ?></p>
            <small><?= $summary['accounts']['count'] ?> accounts</small>
        </article>
        <article class="card">
            <h3>Categories</h3>
            <p><?= $summary['categories'] ?> total</p>
            <small>Income, expense, transfer mapped</small>
        </article>
        <article class="card">
            <h3>Transactions</h3>
            <p><?= number_format($summary['transactions']) ?> entries</p>
            <small>Ledger history</small>
        </article>
        <article class="card">
            <h3>Reminders</h3>
            <p><?= $summary['reminders'] ?></p>
            <small>Upcoming bills/EMIs</small>
        </article>
        <article class="card">
            <h3>Loans</h3>
            <p><?= formatCurrency($summary['loans']['principal']) ?></p>
            <small><?= $summary['loans']['count'] ?> loans tracked</small>
        </article>
        <article class="card">
            <h3>Credit cards</h3>
            <p><?= formatCurrency($summary['credit_cards']['total_limit']) ?> limit</p>
            <small>Outstanding <?= formatCurrency($summary['credit_cards']['total_outstanding']) ?></small>
        </article>
        <article class="card">
            <h3>Lending</h3>
            <p><?= formatCurrency($summary['lending']['outstanding']) ?></p>
            <small><?= $summary['lending']['count'] ?> records</small>
        </article>
        <article class="card">
            <h3>Investments</h3>
            <p><?= $summary['investments']['count'] ?> items</p>
        </article>
        <article class="card">
            <h3>Rental contracts</h3>
            <p><?= $summary['rentals']['contracts'] ?></p>
        </article>
    </section>

    <section class="module-panel">
        <h2>Recent transactions</h2>
        <?php if (empty($recentTransactions)): ?>
            <p class="muted">No transactions yet. Create income, expense, or transfer entries to start building the ledger.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Category</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTransactions as $txn): ?>
                            <tr>
                                <td><?= htmlspecialchars($txn['transaction_date']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($txn['transaction_type'])) ?></td>
                                <td><?= formatCurrency((float) $txn['amount']) ?></td>
                                <td>
                                    <?= htmlspecialchars($txn['category_name'] ?? 'Uncategorized') ?>
                                    <?php if (!empty($txn['subcategory_name'])): ?>
                                        <small class="muted">(<?= htmlspecialchars($txn['subcategory_name']) ?>)</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($txn['notes'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="module-panel">
        <h2>Upcoming reminders</h2>
        <?php if (empty($upcomingReminders)): ?>
            <p class="muted">No reminders scheduled.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Due date</th>
                            <th>Frequency</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingReminders as $reminder): ?>
                            <tr>
                                <td><?= htmlspecialchars($reminder['name']) ?></td>
                                <td><?= htmlspecialchars($reminder['next_due_date']) ?></td>
                                <td><?= htmlspecialchars($reminder['frequency']) ?></td>
                                <td><?= formatCurrency((float) $reminder['amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="module-panel">
        <h2>Upcoming EMIs</h2>
        <?php if (empty($upcomingEmis)): ?>
            <p class="muted">Loan EMIs will appear here once loans are created.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Loan</th>
                            <th>Due date</th>
                            <th>Principal</th>
                            <th>Interest</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingEmis as $emi): ?>
                            <tr>
                                <td><?= htmlspecialchars($emi['loan_name']) ?></td>
                                <td><?= htmlspecialchars($emi['emi_date']) ?></td>
                                <td><?= formatCurrency((float) $emi['principal_component']) ?></td>
                                <td><?= formatCurrency((float) $emi['interest_component']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
