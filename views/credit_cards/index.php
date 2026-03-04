<?php
$activeModule = 'credit_cards';
$cards = $cards ?? [];
$summary = $summary ?? ['count' => 0, 'total_limit' => 0.0, 'total_outstanding' => 0.0];

include __DIR__ . '/../partials/nav.php';
?>
<main class="module-content">
    <header class="module-header">
        <h1>Credit cards</h1>
        <p>Track limits, billing cycles, and outstanding balances without mixing with ledger entries.</p>
    </header>

    <section class="summary-cards">
        <article class="card">
            <h3>Total cards</h3>
            <p><?= $summary['count'] ?></p>
        </article>
        <article class="card">
            <h3>Total limit</h3>
            <p><?= formatCurrency($summary['total_limit']) ?></p>
        </article>
        <article class="card">
            <h3>Outstanding</h3>
            <p><?= formatCurrency($summary['total_outstanding']) ?></p>
        </article>
    </section>

    <section class="module-panel">
        <h2>Add credit card</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="credit_card">
            <label>
                Bank name
                <input type="text" name="bank_name" required>
            </label>
            <label>
                Card name
                <input type="text" name="card_name" required>
            </label>
            <label>
                Credit limit
                <input type="number" name="credit_limit" step="0.01" min="0" required>
            </label>
            <label>
                Billing date (day of month)
                <input type="number" name="billing_date" min="1" max="28" value="1" required>
            </label>
            <label>
                Due date (day of month)
                <input type="number" name="due_date" min="1" max="28" value="1" required>
            </label>
            <label>
                Outstanding balance
                <input type="number" name="outstanding_balance" step="0.01" min="0" value="0">
            </label>
            <button type="submit">Save card</button>
        </form>
    </section>

    <section class="module-panel">
        <h2>Card list</h2>
        <?php if (empty($cards)): ?>
            <p class="muted">No credit cards tracked yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Bank</th>
                            <th>Card</th>
                            <th>Limit</th>
                            <th>Outstanding</th>
                            <th>Billing</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cards as $card): ?>
                            <tr>
                                <td><?= htmlspecialchars($card['bank_name']) ?></td>
                                <td><?= htmlspecialchars($card['card_name']) ?></td>
                                <td><?= formatCurrency((float) $card['credit_limit']) ?></td>
                                <td><?= formatCurrency((float) $card['outstanding_balance']) ?></td>
                                <td><?= htmlspecialchars($card['billing_date']) ?> / <?= htmlspecialchars($card['due_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
