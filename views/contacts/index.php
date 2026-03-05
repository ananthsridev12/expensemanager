<?php
$activeModule = 'contacts';
$contacts = $contacts ?? [];

include __DIR__ . '/../partials/nav.php';
?>
<main class="module-content">
    <header class="module-header">
        <h1>Contacts</h1>
        <p>Master contact book for lending, rental, and future person-based transactions.</p>
    </header>

    <section class="module-panel">
        <h2>Add contact</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="contact">
            <label>
                Name
                <input type="text" name="name" required>
            </label>
            <label>
                Mobile
                <input type="text" name="mobile">
            </label>
            <label>
                Email
                <input type="email" name="email">
            </label>
            <label>
                Address
                <input type="text" name="address">
            </label>
            <label>
                City
                <input type="text" name="city">
            </label>
            <label>
                State
                <input type="text" name="state">
            </label>
            <label>
                Contact type
                <select name="contact_type">
                    <option value="other" selected>Other</option>
                    <option value="tenant">Tenant</option>
                    <option value="lending">Lending</option>
                    <option value="both">Both</option>
                </select>
            </label>
            <label>
                Notes
                <textarea name="notes" rows="2"></textarea>
            </label>
            <button type="submit">Save contact</button>
        </form>
    </section>

    <section class="module-panel">
        <h2>Contact list</h2>
        <?php if (empty($contacts)): ?>
            <p class="muted">No contacts yet.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>City</th>
                            <th>State</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $contact): ?>
                            <tr>
                                <td><?= htmlspecialchars($contact['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($contact['mobile'] ?? '') ?></td>
                                <td><?= htmlspecialchars($contact['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars(ucfirst((string) ($contact['contact_type'] ?? 'other'))) ?></td>
                                <td><?= htmlspecialchars($contact['city'] ?? '') ?></td>
                                <td><?= htmlspecialchars($contact['state'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
