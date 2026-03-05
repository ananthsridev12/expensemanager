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
            <p><?= formatCurrency($accountSummary['total_balance']) ?></p>
        </article>
    </section>

    <section class="module-panel">
        <h2>Add a new account</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="account">
            <label>
                Account type
                <select name="account_type" id="account-type" required>
                    <option value="savings">Savings</option>
                    <option value="current">Current</option>
                    <option value="credit_card">Credit card</option>
                    <option value="cash">Cash</option>
                    <option value="other">Other</option>
                </select>
            </label>
            <label>
                Bank name
                <input type="text" name="bank_name" required>
            </label>
            <label>
                Account name
                <input type="text" name="account_name" required>
            </label>
            <div id="bank-fields" class="module-form">
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
            </div>
            <div id="credit-card-fields" class="module-form" style="display: none;">
                <label>
                    Card name
                    <input type="text" name="card_name">
                </label>
                <label>
                    Credit limit
                    <input type="number" name="credit_limit" step="0.01" min="0" value="0.00">
                </label>
                <label>
                    Billing date (day)
                    <input type="number" name="billing_date" min="1" max="28" value="1">
                </label>
                <label>
                    Due date (day)
                    <input type="number" name="due_date" min="1" max="28" value="1">
                </label>
                <label>
                    Outstanding balance
                    <input type="number" name="outstanding_balance" step="0.01" min="0" value="0.00">
                </label>
                <label>
                    Outstanding principal
                    <input type="number" name="outstanding_principal" step="0.01" min="0" value="0.00">
                </label>
                <label>
                    Interest rate (% p.a.)
                    <input type="number" name="interest_rate" step="0.01" min="0" value="0">
                </label>
                <label>
                    Tenure months
                    <input type="number" name="tenure_months" min="0" value="0">
                </label>
                <label>
                    Processing fee
                    <input type="number" name="processing_fee" step="0.01" min="0" value="0.00">
                </label>
                <label>
                    GST rate (%)
                    <input type="number" name="gst_rate" step="0.01" min="0" value="18.00">
                </label>
                <label>
                    EMI amount (optional)
                    <input type="number" name="emi_amount" step="0.01" min="0" value="0.00">
                </label>
                <label>
                    EMI start date
                    <input type="date" name="emi_start_date">
                </label>
            </div>
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
                            <th>Type</th>
                            <th>Bank</th>
                            <th>Name</th>
                            <th>Balance</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $account): ?>
                            <tr>
                                <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $account['account_type'] ?? 'savings'))) ?></td>
                                <td><?= htmlspecialchars($account['bank_name']) ?></td>
                                <td><?= htmlspecialchars($account['account_name']) ?></td>
                                <td>
                                    <?php if (($account['account_type'] ?? '') === 'credit_card'): ?>
                                        <?= formatCurrency((float) ($account['outstanding_balance'] ?? 0)) ?> / <?= formatCurrency((float) ($account['credit_limit'] ?? 0)) ?>
                                    <?php else: ?>
                                        <?= formatCurrency((float) ($account['balance'] ?? 0)) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($account['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <script>
        (function () {
            const typeSelect = document.getElementById('account-type');
            const bankFields = document.getElementById('bank-fields');
            const cardFields = document.getElementById('credit-card-fields');

            function toggleAccountFields() {
                const isCreditCard = typeSelect.value === 'credit_card';
                bankFields.style.display = isCreditCard ? 'none' : 'grid';
                cardFields.style.display = isCreditCard ? 'grid' : 'none';
            }

            typeSelect.addEventListener('change', toggleAccountFields);
            toggleAccountFields();
        })();
    </script>
</main>
