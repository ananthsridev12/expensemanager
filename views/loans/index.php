<?php
$activeModule = 'loans';
$loans = $loans ?? [];
$upcomingEmis = $upcomingEmis ?? [];
$summary = $summary ?? ['count' => 0, 'total_principal' => 0.0];

include __DIR__ . '/../partials/nav.php';
?>
<main class="module-content">
    <header class="module-header">
        <h1>Loans</h1>
        <p>Track principal, EMI schedules, and repayments while keeping the ledger immutable.</p>
    </header>

    <section class="summary-cards">
        <article class="card">
            <h3>Active loans</h3>
            <p><?= $summary['count'] ?></p>
        </article>
        <article class="card">
            <h3>Total principal</h3>
            <p>? <?= number_format($summary['total_principal'], 2) ?></p>
        </article>
    </section>

    <section class="module-panel">
        <h2>New loan</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="loan">
            <label>
                Loan type
                <select name="loan_type">
                    <option value="personal" selected>Personal Loan</option>
                    <option value="home">Home Loan</option>
                    <option value="car">Car Loan</option>
                    <option value="gold">Gold Loan</option>
                </select>
            </label>
            <label>
                Loan name
                <input type="text" name="loan_name" required>
            </label>
            <label>
                Principal amount
                <input type="number" name="principal_amount" step="0.01" required>
            </label>
            <label>
                Interest rate (% annual)
                <input type="number" name="interest_rate" step="0.01" required>
            </label>
            <label>
                Tenure (months)
                <input type="number" name="tenure_months" min="1" required>
            </label>
            <label>
                Processing fee
                <input type="number" name="processing_fee" step="0.01">
            </label>
            <label>
                GST
                <input type="number" name="gst" step="0.01">
            </label>
            <label>
                Start date
                <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" required>
            </label>
            <button type="submit">Create loan</button>
        </form>
    </section>

    <section class="module-panel">
        <h2>Loan list</h2>
        <?php if (empty($loans)): ?>
            <p class="muted">No loans recorded yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Principal</th>
                            <th>EMI</th>
                            <th>Start date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loans as $loan): ?>
                            <tr>
                                <td><?= htmlspecialchars($loan['loan_name']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($loan['loan_type'])) ?></td>
                                <td>? <?= number_format((float) $loan['principal_amount'], 2) ?></td>
                                <td>? <?= number_format((float) $loan['emi_amount'], 2) ?></td>
                                <td><?= htmlspecialchars($loan['start_date']) ?></td>
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
            <p class="muted">Loan EMIs will appear here once created.</p>
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
                                <td>? <?= number_format((float) $emi['principal_component'], 2) ?></td>
                                <td>? <?= number_format((float) $emi['interest_component'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
