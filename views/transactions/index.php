<?php
$activeModule = 'transactions';
$accounts = $accounts ?? [];
$loans = $loans ?? [];
$categories = $categories ?? [];
$recentTransactions = $recentTransactions ?? [];
$totalsByType = $totalsByType ?? [];
$imported = $imported ?? null;
$failed = $failed ?? null;
$paymentMethods = $paymentMethods ?? [];
$purchaseParents = $purchaseParents ?? [];
$purchaseChildren = $purchaseChildren ?? [];

include __DIR__ . '/../partials/nav.php';
?>
<main class="module-content">
    <header class="module-header">
        <h1>Transactions</h1>
        <p>Every money movement hits the master ledger with immutable history.</p>
    </header>

    <?php if ($imported !== null || $failed !== null): ?>
        <section class="module-panel">
            <strong>Import result:</strong>
            <span class="muted">Imported <?= (int) ($imported ?? 0) ?> row(s), failed <?= (int) ($failed ?? 0) ?> row(s).</span>
        </section>
    <?php endif; ?>

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
            <label>
                Payment method
                <select name="payment_method_id" id="payment-method-select">
                    <option value="">Select method</option>
                    <?php foreach ($paymentMethods as $method): ?>
                        <option value="<?= (int) $method['id'] ?>"><?= htmlspecialchars($method['name']) ?></option>
                    <?php endforeach; ?>
                    <option value="other">Other (add new)</option>
                </select>
            </label>
            <label id="new-payment-method-wrap" style="display: none;">
                New payment method
                <input type="text" name="new_payment_method" placeholder="Example: UPI Lite">
            </label>
            <label>
                To whom (Contact)
                <input type="text" id="transaction-contact-search" placeholder="Type name/mobile/email" autocomplete="off">
                <input type="hidden" name="contact_id" id="transaction-contact-id">
            </label>
            <div class="module-placeholder" id="transaction-contact-results">
                <small class="muted">Start typing to search contacts.</small>
            </div>
            <label>
                Purchased from category
                <select name="purchase_parent_id" id="purchase-parent-select">
                    <option value="">Select group</option>
                    <?php foreach ($purchaseParents as $parent): ?>
                        <option value="<?= (int) $parent['id'] ?>"><?= htmlspecialchars($parent['name']) ?></option>
                    <?php endforeach; ?>
                    <option value="other">Other group (add new)</option>
                </select>
            </label>
            <label id="new-purchase-parent-wrap" style="display: none;">
                New purchase group
                <input type="text" name="new_purchase_parent" placeholder="Example: Temple / Donations">
            </label>
            <label>
                Purchased from
                <select name="purchase_source_id" id="purchase-source-select">
                    <option value="">Select source</option>
                    <?php foreach ($purchaseChildren as $source): ?>
                        <option value="<?= (int) $source['id'] ?>" data-parent="<?= (int) $source['parent_id'] ?>">
                            <?= htmlspecialchars($source['name']) ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="other">Other source (add new)</option>
                </select>
            </label>
            <label id="new-purchase-source-wrap" style="display: none;">
                New purchase source
                <input type="text" name="new_purchase_source" placeholder="Example: Local Tea Stall">
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
        <h2>Import transactions (CSV)</h2>
        <p class="muted">
            Upload CSV with header columns:
            <code>transaction_date,account_token,transaction_type,amount,category_id,subcategory_id,payment_method_id,payment_method_name,contact_id,purchase_parent_id,purchase_parent_name,purchase_source_id,purchase_source_name,notes,transfer_to_account_token</code>.
            Use account token format like <code>savings:1</code>, <code>credit_card:3</code>, <code>loan:2</code>.
        </p>
        <p class="muted">
            For transfer rows, fill <code>transfer_to_account_token</code>. You can also use account_id/account_type columns instead of account_token.
        </p>
        <p class="muted">
            Download sample:
            <a href="public/templates/transactions_import_template.csv" target="_blank" rel="noopener">transactions_import_template.csv</a>
        </p>
        <form method="post" enctype="multipart/form-data" class="module-form">
            <input type="hidden" name="form" value="transaction_import">
            <label>
                CSV file
                <input type="file" name="transaction_file" accept=".csv,text/csv" required>
            </label>
            <button type="submit">Import CSV</button>
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
                            <th>Payment</th>
                            <th>To whom</th>
                            <th>Purchased from</th>
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
                                <td><?= htmlspecialchars($txn['payment_method_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($txn['contact_name'] ?? '-') ?></td>
                                <td>
                                    <?= htmlspecialchars($txn['purchase_source_name'] ?? '-') ?>
                                    <?php if (!empty($txn['purchase_parent_name'])): ?>
                                        <small class="muted">-> <?= htmlspecialchars($txn['purchase_parent_name']) ?></small>
                                    <?php endif; ?>
                                </td>
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
            const paymentMethodSelect = document.getElementById('payment-method-select');
            const newPaymentMethodWrap = document.getElementById('new-payment-method-wrap');
            const purchaseParentSelect = document.getElementById('purchase-parent-select');
            const purchaseSourceSelect = document.getElementById('purchase-source-select');
            const newPurchaseParentWrap = document.getElementById('new-purchase-parent-wrap');
            const newPurchaseSourceWrap = document.getElementById('new-purchase-source-wrap');
            const contactSearchInput = document.getElementById('transaction-contact-search');
            const contactIdInput = document.getElementById('transaction-contact-id');
            const contactResultsWrap = document.getElementById('transaction-contact-results');

            const storedOptions = Array.from(subcategorySelect.querySelectorAll('option[data-category]')).map(option => ({
                value: option.value,
                label: option.innerHTML,
                category: option.dataset.category,
            }));
            const storedPurchaseSources = Array.from(purchaseSourceSelect.querySelectorAll('option[data-parent]')).map(option => ({
                value: option.value,
                label: option.innerHTML,
                parent: option.dataset.parent,
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

            function togglePaymentMethodOther() {
                newPaymentMethodWrap.style.display = paymentMethodSelect.value === 'other' ? 'flex' : 'none';
                if (paymentMethodSelect.value === 'other') {
                    paymentMethodSelect.name = '';
                    newPaymentMethodWrap.querySelector('input').focus();
                    return;
                }
                paymentMethodSelect.name = 'payment_method_id';
            }

            function refreshPurchaseSources() {
                const selectedParent = purchaseParentSelect.value;
                purchaseSourceSelect.innerHTML = '<option value="">Select source</option>';

                storedPurchaseSources.forEach(item => {
                    if (!selectedParent || selectedParent === 'other' || item.parent === selectedParent) {
                        const option = document.createElement('option');
                        option.value = item.value;
                        option.innerHTML = item.label;
                        option.dataset.parent = item.parent;
                        purchaseSourceSelect.appendChild(option);
                    }
                });

                const other = document.createElement('option');
                other.value = 'other';
                other.innerHTML = 'Other source (add new)';
                purchaseSourceSelect.appendChild(other);
            }

            function togglePurchaseOther() {
                newPurchaseParentWrap.style.display = purchaseParentSelect.value === 'other' ? 'flex' : 'none';
                newPurchaseSourceWrap.style.display = purchaseSourceSelect.value === 'other' ? 'flex' : 'none';

                if (purchaseParentSelect.value === 'other') {
                    purchaseParentSelect.name = '';
                } else {
                    purchaseParentSelect.name = 'purchase_parent_id';
                }

                if (purchaseSourceSelect.value === 'other') {
                    purchaseSourceSelect.name = '';
                } else {
                    purchaseSourceSelect.name = 'purchase_source_id';
                }
            }

            function renderContactResults(items) {
                if (!items.length) {
                    contactResultsWrap.innerHTML = '<small class="muted">No contacts found.</small>';
                    return;
                }

                contactResultsWrap.innerHTML = '';
                items.forEach(item => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'secondary';
                    button.style.marginRight = '0.5rem';
                    button.style.marginBottom = '0.5rem';
                    button.textContent = item.name + (item.mobile ? ' - ' + item.mobile : '');
                    button.addEventListener('click', function () {
                        contactIdInput.value = item.id;
                        contactSearchInput.value = item.name + (item.mobile ? ' - ' + item.mobile : '');
                        contactResultsWrap.innerHTML = '<small class="muted">Selected: ' + button.textContent + '</small>';
                    });
                    contactResultsWrap.appendChild(button);
                });
            }

            async function searchContacts(query) {
                const response = await fetch('?module=transactions&action=contact_search&q=' + encodeURIComponent(query));
                if (!response.ok) {
                    return;
                }
                const data = await response.json();
                renderContactResults(Array.isArray(data) ? data : []);
            }

            typeSelect.addEventListener('change', toggleTransferFields);
            typeSelect.addEventListener('change', toggleEmiFields);
            accountSelect.addEventListener('change', toggleEmiFields);
            emiToggleSelect.addEventListener('change', toggleEmiFields);
            categorySelect.addEventListener('change', refreshSubcategories);
            paymentMethodSelect.addEventListener('change', togglePaymentMethodOther);
            purchaseParentSelect.addEventListener('change', function () {
                refreshPurchaseSources();
                togglePurchaseOther();
            });
            purchaseSourceSelect.addEventListener('change', togglePurchaseOther);
            contactSearchInput.addEventListener('input', function () {
                const query = contactSearchInput.value.trim();
                if (query.length < 2) {
                    contactIdInput.value = '';
                    contactResultsWrap.innerHTML = '<small class="muted">Start typing to search contacts.</small>';
                    return;
                }
                searchContacts(query);
            });

            toggleTransferFields();
            toggleEmiFields();
            refreshSubcategories();
            togglePaymentMethodOther();
            refreshPurchaseSources();
            togglePurchaseOther();
        })();
    </script>
</main>
