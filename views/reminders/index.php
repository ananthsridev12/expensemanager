<?php
$activeModule = 'reminders';
$upcoming = $upcoming ?? [];
$total = $total ?? 0;

include __DIR__ . '/../partials/nav.php';
?>
<main class="module-content">
    <header class="module-header">
        <h1>Reminders</h1>
        <p>Keep track of upcoming bills, EMIs, and recurring obligations.</p>
    </header>

    <section class="summary-cards">
        <article class="card">
            <h3>Total reminders</h3>
            <p><?= $total ?></p>
        </article>
        <article class="card">
            <h3>Next due</h3>
            <?php if (!empty($upcoming)): ?>
                <p><?= htmlspecialchars($upcoming[0]['name']) ?></p>
                <small><?= htmlspecialchars($upcoming[0]['next_due_date']) ?></small>
            <?php else: ?>
                <p class="muted">None scheduled</p>
            <?php endif; ?>
        </article>
    </section>

    <section class="module-panel">
        <h2>New reminder</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="reminder">
            <label>
                Name
                <input type="text" name="name" required>
            </label>
            <label>
                Amount
                <input type="number" name="amount" step="0.01" min="0">
            </label>
            <label>
                Frequency
                <select name="frequency">
                    <option value="once">Once</option>
                    <option value="monthly" selected>Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </label>
            <label>
                Next due date
                <input type="date" name="next_due_date" value="<?= date('Y-m-d') ?>" required>
            </label>
            <label>
                Status
                <select name="status">
                    <option value="upcoming" selected>Upcoming</option>
                    <option value="completed">Completed</option>
                    <option value="missed">Missed</option>
                </select>
            </label>
            <label>
                Notes
                <textarea name="notes" rows="2"></textarea>
            </label>
            <button type="submit">Save reminder</button>
        </form>
    </section>

    <section class="module-panel">
        <h2>Upcoming reminders</h2>
        <?php if (empty($upcoming)): ?>
            <p class="muted">No reminders scheduled yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Due</th>
                            <th>Frequency</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming as $reminder): ?>
                            <tr>
                                <td><?= htmlspecialchars($reminder['name']) ?></td>
                                <td><?= htmlspecialchars($reminder['next_due_date']) ?></td>
                                <td><?= htmlspecialchars($reminder['frequency']) ?></td>
                                <td>? <?= number_format((float) $reminder['amount'], 2) ?></td>
                                <td><?= htmlspecialchars(ucfirst($reminder['status'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
