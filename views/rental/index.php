<?php
$activeModule = 'rental';
$properties = $properties ?? [];
$tenants = $tenants ?? [];
$contracts = $contracts ?? [];
$transactions = $transactions ?? [];
$upcoming = $upcoming ?? [];
$summary = $summary ?? ['properties' => 0, 'tenants' => 0, 'contracts' => 0];

include __DIR__ . '/../partials/nav.php';
?>
<main class="module-content">
    <header class="module-header">
        <h1>Rental management</h1>
        <p>Manage properties, tenants, contracts, and rental income in one ledger.</p>
    </header>

    <section class="summary-cards">
        <article class="card">
            <h3>Properties</h3>
            <p><?= $summary['properties'] ?></p>
        </article>
        <article class="card">
            <h3>Tenants</h3>
            <p><?= $summary['tenants'] ?></p>
        </article>
        <article class="card">
            <h3>Contracts</h3>
            <p><?= $summary['contracts'] ?></p>
        </article>
    </section>

    <section class="module-panel">
        <h2>New property</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="property">
            <label>
                Name
                <input type="text" name="property_name" required>
            </label>
            <label>
                Monthly rent
                <input type="number" name="monthly_rent" step="0.01" required>
            </label>
            <label>
                Security deposit
                <input type="number" name="security_deposit" step="0.01">
            </label>
            <label>
                Address
                <input type="text" name="address">
            </label>
            <button type="submit">Add property</button>
        </form>
    </section>

    <section class="module-panel">
        <h2>New tenant</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="tenant">
            <label>
                Name
                <input type="text" name="tenant_name" required>
            </label>
            <label>
                Mobile
                <input type="text" name="tenant_mobile">
            </label>
            <label>
                Email
                <input type="email" name="tenant_email">
            </label>
            <label>
                ID proof
                <input type="text" name="tenant_id_proof">
            </label>
            <label>
                Address
                <input type="text" name="tenant_address">
            </label>
            <button type="submit">Add tenant</button>
        </form>
    </section>

    <section class="module-panel">
        <h2>Create rental contract</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="contract">
            <label>
                Property
                <select name="property_id" required>
                    <?php foreach ($properties as $property): ?>
                        <option value="<?= $property['id'] ?>"><?= htmlspecialchars($property['property_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Tenant
                <select name="tenant_id" required>
                    <?php foreach ($tenants as $tenant): ?>
                        <option value="<?= $tenant['id'] ?>"><?= htmlspecialchars($tenant['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Start date
                <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" required>
            </label>
            <label>
                End date
                <input type="date" name="end_date">
            </label>
            <label>
                Rent amount
                <input type="number" name="rent_amount" step="0.01" required>
            </label>
            <label>
                Deposit amount
                <input type="number" name="deposit_amount" step="0.01">
            </label>
            <button type="submit">Create contract</button>
        </form>
    </section>

    <section class="module-panel">
        <h2>Record rent</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="transaction">
            <label>
                Contract
                <select name="contract_id" required>
                    <?php foreach ($contracts as $contract): ?>
                        <option value="<?= $contract['id'] ?>"><?= htmlspecialchars($contract['property_name'] . ' / ' . $contract['tenant_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Rent month
                <input type="month" name="rent_month" value="<?= date('Y-m') ?>">
            </label>
            <label>
                Due date
                <input type="date" name="due_date" value="<?= date('Y-m-d') ?>">
            </label>
            <label>
                Paid amount
                <input type="number" name="paid_amount" step="0.01" required>
            </label>
            <label>
                Status
                <select name="payment_status">
                    <option value="paid">Paid</option>
                    <option value="pending" selected>Pending</option>
                    <option value="overdue">Overdue</option>
                </select>
            </label>
            <label>
                Notes
                <textarea name="notes" rows="2"></textarea>
            </label>
            <button type="submit">Save rent</button>
        </form>
    </section>

    <section class="module-panel">
        <h2>Contracts</h2>
        <?php if (empty($contracts)): ?>
            <p class="muted">No rental contracts yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Tenant</th>
                            <th>Rent</th>
                            <th>Period</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contracts as $contract): ?>
                            <tr>
                                <td><?= htmlspecialchars($contract['property_name']) ?></td>
                                <td><?= htmlspecialchars($contract['tenant_name']) ?></td>
                                <td><?= formatCurrency((float) $contract['rent_amount']) ?></td>
                                <td><?= htmlspecialchars($contract['start_date']) ?> → <?= htmlspecialchars($contract['end_date'] ?? 'ongoing') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="module-panel">
        <h2>Transactions</h2>
        <?php if (empty($transactions)): ?>
            <p class="muted">No rental payments yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Property</th>
                            <th>Paid</th>
                            <th>Status</th>
                            <th>Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $txn): ?>
                            <tr>
                                <td><?= htmlspecialchars($txn['tenant_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($txn['property_name'] ?? '') ?></td>
                                <td><?= formatCurrency((float) $txn['paid_amount']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($txn['payment_status'])) ?></td>
                                <td><?= htmlspecialchars($txn['due_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="module-panel">
        <h2>Upcoming rent due</h2>
        <?php if (empty($upcoming)): ?>
            <p class="muted">Nothing upcoming for now.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Property</th>
                            <th>Due date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming as $due): ?>
                            <tr>
                                <td><?= htmlspecialchars($due['tenant_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($due['property_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($due['due_date']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($due['payment_status'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
