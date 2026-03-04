<?php
$activeModule = 'sip';
$schedules = $schedules ?? [];
$upcoming = $upcoming ?? [];
$investments = $investments ?? [];
$accounts = $accounts ?? [];

include __DIR__ . '/../partials/nav.php';
?>
<main class="module-content">
    <header class="module-header">
        <h1>SIP</h1>
        <p>Schedule recurring investments so the ledger records each SIP debit automatically.</p>
    </header>

    <section class="module-panel">
        <h2>New SIP schedule</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="sip">
            <label>
                Investment
                <select name="investment_id" required>
                    <?php foreach ($investments as $investment): ?>
                        <option value="<?= $investment['id'] ?>"><?= htmlspecialchars($investment['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Account
                <select name="account_id" required>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['bank_name'] . ' - ' . $account['account_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                SIP amount
                <input type="number" name="sip_amount" step="0.01" required>
            </label>
            <label>
                SIP day
                <input type="number" name="sip_day" min="1" max="28" value="1">
            </label>
            <label>
                Frequency
                <select name="frequency">
                    <option value="monthly" selected>Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </label>
            <label>
                Start date
                <input type="date" name="start_date" value="<?= date('Y-m-d') ?>">
            </label>
            <label>
                End date
                <input type="date" name="end_date">
            </label>
            <label>
                Next run date
                <input type="date" name="next_run_date" value="<?= date('Y-m-d') ?>">
            </label>
            <label>
                Status
                <select name="status">
                    <option value="active" selected>Active</option>
                    <option value="paused">Paused</option>
                    <option value="ended">Ended</option>
                </select>
            </label>
            <button type="submit">Schedule SIP</button>
        </form>
    </section>

    <section class="module-panel">
        <h2>Schedules</h2>
        <?php if (empty($schedules)): ?>
            <p class="muted">No SIP schedules created yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Investment</th>
                            <th>Amount</th>
                            <th>Frequency</th>
                            <th>Next run</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td><?= htmlspecialchars($schedule['investment_name'] ?? '?') ?></td>
                                <td><?= formatCurrency((float) $schedule['sip_amount']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($schedule['frequency'])) ?></td>
                                <td><?= htmlspecialchars($schedule['next_run_date'] ?? '?') ?></td>
                                <td><?= htmlspecialchars(ucfirst($schedule['status'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="module-panel">
        <h2>Upcoming SIP run</h2>
        <?php if (empty($upcoming)): ?>
            <p class="muted">No upcoming SIP runs yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Investment</th>
                            <th>Next run</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming as $schedule): ?>
                            <tr>
                                <td><?= htmlspecialchars($schedule['investment_name'] ?? '?') ?></td>
                                <td><?= htmlspecialchars($schedule['next_run_date']) ?></td>
                                <td><?= formatCurrency((float) $schedule['sip_amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
