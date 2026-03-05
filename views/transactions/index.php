<?php
$activeModule = 'transactions';
$accounts = $accounts ?? [];
$loans = $loans ?? [];
$categories = $categories ?? [];
$recentTransactions = $recentTransactions ?? [];
$totalsByType = $totalsByType ?? [];

include __DIR__ . '/../partials/nav.php';
?>
<main class="module-content">
    <header class="module-header">
        <h1>Transactions</h1>
        <p>Every money movement hits the master ledger with immutable history.</p>
    </header>

    <section class="summary-cards">
        <?php foreach (['income', 'expense', 'transfer'] as $type): ?>
            <article class="card">
                <h3><?= ucfirst($type) ?></h3>
                <p><?= formatCurrency($totalsByType[$type] ?? 0.00) ?></p>
                <small>Ledger total</small>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="module-panel">
        <h2>Add transaction</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="transaction">
            <label>
                Date
                <input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required>
            </label>
            <label>
                From account
                <select name="account_id" required>
                    <?php foreach ($accounts as $account): ?>
                        <?php
                        $accountType = $account['account_type'] ?? 'bank';
                        $label = $accountType === 'credit_card'
                            ? 'Card: ' . $account['bank_name'] . ' - ' . $account['account_name']
                            : $account['bank_name'] . ' - ' . $account['account_name'];
                        ?>
                        <option value="<?= $accountType . ':' . $account['id'] ?>" data-type="<?= htmlspecialchars($accountType) ?>">
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                    <?php foreach ($loans as $loan): ?>
                        <option value="loan:<?= (int) $loan['id'] ?>" data-type="loan">
                            <?= htmlspecialchars('Loan: ' . ($loan['loan_name'] ?? 'Loan #' . $loan['id'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Transaction type
                <select name="transaction_type" id="transaction-type">
                    <option value="income">Income</option>
                    <option value="expense" selected>Expense</option>
                    <option value="transfer">Transfer</option>
                </select>
            </label>
            <label>
                Amount
                <input type="number" name="amount" step="0.01" min="0" required>
            </label>
            <label id="emi-toggle-wrap" style="display: none;">
                EMI purchase?
                <select name="is_emi_purchase" id="is-emi-purchase">
                    <option value="no" selected>No</option>
                    <option value="yes">Yes</option>
                </select>
            </label>
            <div id="emi-fields" style="display: none;">
                <div class="module-form">
                    <label>
                        EMI name
                        <input type="text" name="emi_name" placeholder="Phone EMI">
                    </label>
                    <label>
                        Interest rate (% p.a.)
                        <input type="number" name="interest_rate" step="0.01" min="0" value="0">
                    </label>
                    <label>
                        Total EMIs
                        <input type="number" name="total_emis" min="1" value="1">
                    </label>
                    <label>
                        EMI start date
                        <input type="date" name="emi_date">
                    </label>
                    <label>
                        Processing fee
                        <input type="number" name="processing_fee" step="0.01" min="0" value="0">
                    </label>
                    <label>
                        GST rate (%)
                        <input type="number" name="gst_rate" step="0.01" min="0" value="18">
                    </label>
                </div>
            </div>
            <label>
                Category
                <select name="category_id" id="category-select">
                    <option value="">Uncategorized</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?> (<?= $category['type'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Subcategory
                <select name="subcategory_id" id="subcategory-select">
                    <option value="">None</option>
                    <?php foreach ($categories as $category): ?>
                        <?php foreach ($category['subcategories'] as $sub): ?>
                            <option value="<?= $sub['id'] ?>" data-category="<?= $category['id'] ?>"><?= htmlspecialchars($category['name'] . ' - ' . $sub['name']) ?></option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="module-form" id="transfer-options" style="display: none;">
                <label>
                    To account
                    <select name="transfer_to_account_id">
                        <option value="">Select target account</option>
                        <?php foreach ($accounts as $account): ?>
                            <?php $accountType = $account['account_type'] ?? 'bank'; ?>
                            <option value="<?= $accountType . ':' . $account['id'] ?>"><?= htmlspecialchars($account['bank_name'] . ' - ' . $account['account_name']) ?></option>
                        <?php endforeach; ?>
                        <?php foreach ($loans as $loan): ?>
                            <option value="loan:<?= (int) $loan['id'] ?>"><?= htmlspecialchars('Loan: ' . ($loan['loan_name'] ?? 'Loan #' . $loan['id'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <label>
                Reference type
                <input type="text" name="reference_type">
            </label>
            <label>
                Reference ID
                <input type="number" name="reference_id" min="0">
            </label>
            <label>
                Notes
                <textarea name="notes" rows="2"></textarea>
            </label>
            <button type="submit">Record transaction</button>
        </form>
    </section>

    <section class="module-panel">
        <h2>Recent ledger entries</h2>
        <?php if (empty($recentTransactions)): ?>
            <p class="muted">No transactions have been recorded yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Bank</th>
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
                                <td><?= htmlspecialchars(trim(($txn['bank_name'] ?? '-') . ' - ' . ($txn['account_name'] ?? ''))) ?></td>
                                <td><?= htmlspecialchars(ucfirst($txn['transaction_type'])) ?></td>
                                <td><?= formatCurrency((float) $txn['amount']) ?></td>
                                <td>
                                    <?= htmlspecialchars($txn['category_name'] ?? 'Uncategorized') ?>
                                    <?php if (!empty($txn['subcategory_name'])): ?>
                                        <small class="muted">-> <?= htmlspecialchars($txn['subcategory_name']) ?></small>
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

    <script>
        (function () {
            const typeSelect = document.getElementById('transaction-type');
            const accountSelect = document.querySelector('select[name=\"account_id\"]');
            const transferPanel = document.getElementById('transfer-options');
            const emiToggleWrap = document.getElementById('emi-toggle-wrap');
            const emiToggleSelect = document.getElementById('is-emi-purchase');
            const emiFields = document.getElementById('emi-fields');
            const categorySelect = document.getElementById('category-select');
            const subcategorySelect = document.getElementById('subcategory-select');

            const storedOptions = Array.from(subcategorySelect.querySelectorAll('option[data-category]')).map(option => ({
                value: option.value,
                label: option.innerHTML,
                category: option.dataset.category,
            }));

            function toggleTransferFields() {
                transferPanel.style.display = typeSelect.value === 'transfer' ? 'grid' : 'none';
            }

            function toggleEmiFields() {
                const selectedOption = accountSelect.options[accountSelect.selectedIndex];
                const isCard = selectedOption && selectedOption.dataset.type === 'credit_card';
                const isExpense = typeSelect.value === 'expense';
                const eligible = isCard && isExpense;
                emiToggleWrap.style.display = eligible ? 'flex' : 'none';

                if (!eligible) {
                    emiToggleSelect.value = 'no';
                    emiFields.style.display = 'none';
                    return;
                }

                emiFields.style.display = emiToggleSelect.value === 'yes' ? 'block' : 'none';
            }

            function refreshSubcategories() {
                const selectedCategory = categorySelect.value;
                subcategorySelect.innerHTML = '<option value="">None</option>';

                storedOptions.forEach(item => {
                    if (!selectedCategory || item.category === selectedCategory) {
                        const option = document.createElement('option');
                        option.value = item.value;
                        option.innerHTML = item.label;
                        option.dataset.category = item.category;
                        subcategorySelect.appendChild(option);
                    }
                });
            }

            typeSelect.addEventListener('change', toggleTransferFields);
            typeSelect.addEventListener('change', toggleEmiFields);
            accountSelect.addEventListener('change', toggleEmiFields);
            emiToggleSelect.addEventListener('change', toggleEmiFields);
            categorySelect.addEventListener('change', refreshSubcategories);

            toggleTransferFields();
            toggleEmiFields();
            refreshSubcategories();
        })();
    </script>
</main>
