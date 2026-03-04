<?php
$activeModule = 'lending';
$records = $records ?? [];
$summary = $summary ?? ['count' => 0, 'outstanding' => 0.0];

include __DIR__ . '/../partials/nav.php';
?>
<main class="module-content">
    <header class="module-header">
        <h1>Lending</h1>
        <p>Track funds you have lent and the outstanding amounts for friends or relatives.</p>
    </header>

    <section class="summary-cards">
        <article class="card">
            <h3>Lending records</h3>
            <p><?= $summary['count'] ?></p>
        </article>
        <article class="card">
            <h3>Outstanding</h3>
            <p><?= formatCurrency($summary['outstanding']) ?></p>
        </article>
    </section>

    <section class="module-panel">
        <h2>New lending record</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="lending">
            <label>
                Contact name
                <input type="text" name="contact_name" required>
            </label>
            <label>
                Mobile
                <input type="text" name="contact_mobile">
            </label>
            <label>
                Email
                <input type="email" name="contact_email">
            </label>
            <label>
                Address
                <input type="text" name="contact_address">
            </label>
            <label>
                City
                <input type="text" name="contact_city">
            </label>
            <label>
                State
                <input type="text" name="contact_state">
            </label>
            <label>
                Lending amount
                <input type="number" name="principal_amount" step="0.01" required>
            </label>
            <label>
                Interest rate
                <input type="number" name="interest_rate" step="0.01" required>
            </label>
            <label>
                Lending date
                <input type="date" name="lending_date" value="<?= date('Y-m-d') ?>" required>
            </label>
            <label>
                Due date
                <input type="date" name="due_date">
            </label>
            <label>
                Total repaid
                <input type="number" name="total_repaid" step="0.01" value="0">
            </label>
            <label>
                Status
                <select name="status">
                    <option value="ongoing" selected>Ongoing</option>
                    <option value="closed">Closed</option>
                    <option value="defaulted">Defaulted</option>
                </select>
            </label>
            <label>
                Notes
                <textarea name="notes" rows="2"></textarea>
            </label>
            <button type="submit">Record lending</button>
        </form>
    </section>

    <section class="module-panel">
        <h2>Lending ledger</h2>
        <?php if (empty($records)): ?>
            <p class="muted">No lending records yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Contact</th>
                            <th>Principal</th>
                            <th>Interest</th>
                            <th>Outstanding</th>
                            <th>Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($record['contact_name']) ?></strong><br>
                                    <small><?= htmlspecialchars($record['mobile'] ?? '') ?></small>
                                </td>
                                <td><?= formatCurrency((float) $record['principal_amount']) ?></td>
                                <td><?= number_format((float) $record['interest_rate'], 2) ?>%</td>
                                <td><?= formatCurrency((float) $record['outstanding_amount']) ?></td>
                                <td><?= htmlspecialchars($record['due_date'] ?? '?') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
