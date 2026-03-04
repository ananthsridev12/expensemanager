<nav class="main-nav">
    <?php $module = $activeModule ?? 'dashboard'; ?>
    <span class="brand">Easi7 Finance</span>
    <a href="?module=dashboard" class="<?= $module === 'dashboard' ? 'is-active' : '' ?>">Dashboard</a>
    <a href="?module=accounts" class="<?= $module === 'accounts' ? 'is-active' : '' ?>">Accounts</a>
    <a href="?module=categories" class="<?= $module === 'categories' ? 'is-active' : '' ?>">Categories</a>
    <a href="?module=transactions" class="<?= $module === 'transactions' ? 'is-active' : '' ?>">Transactions</a>
    <a href="?module=credit_cards" class="<?= $module === 'credit_cards' ? 'is-active' : '' ?>">Credit Cards</a>
    <a href="?module=reminders" class="<?= $module === 'reminders' ? 'is-active' : '' ?>">Reminders</a>
    <a href="?module=loans" class="<?= $module === 'loans' ? 'is-active' : '' ?>">Loans</a>
    <a href="?module=lending" class="<?= $module === 'lending' ? 'is-active' : '' ?>">Lending</a>
    <a href="?module=investments" class="<?= $module === 'investments' ? 'is-active' : '' ?>">Investments</a>
    <a href="?module=sip" class="<?= $module === 'sip' ? 'is-active' : '' ?>">SIP</a>
    <a href="?module=rental" class="<?= $module === 'rental' ? 'is-active' : '' ?>">Rental</a>
</nav>
