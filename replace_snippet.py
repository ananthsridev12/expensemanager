#!/usr/bin/env python3
from pathlib import Path

path = Path('views/transactions/index.php')
text = path.read_text()
old = '''            <label>
                From account
                <select name="account_id" required>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?= $account['id'] ?>"><?= htmlspecialchars($account['bank_name'] . ' — ' . $account['account_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>'''
new = '''            <label>
                From account
                <select name="account_id" required>
                    <?php foreach ($accounts as $account): ?>
                        <?php $label = ($account['account_type'] ?? 'bank') == 'credit_card' ? 'Card: ' + $account['bank_name'] + ' — ' + $account['account_name'] : $account['bank_name'] + ' — ' + $account['account_name']; ?>
                        <option value="<?= $account['id'] ?>" data-type="<?= $account['account_type'] ?? 'bank' ?>">
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>'''
if old not in text:
    raise SystemExit('snippet missing in file')
path.write_text(text.replace(old, new))
