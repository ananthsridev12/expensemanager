<?php

use Config\Database;

require_once __DIR__ . '/config/database.php';

$database = new Database();
$connection = $database->connect();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Manager Home</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <main>
        <h1>Personal Finance Manager</h1>
        <p>Ledger-driven system ready to grow with bank accounts, transactions, loans, investments, and rental income.</p>
        <section class="module-placeholder">
            <h2>Next Modules</h2>
            <ul>
                <li>Accounts and categories</li>
                <li>Transaction ledger and reminders</li>
                <li>Loans, credit cards, lending, investments</li>
                <li>SIP automation and rental dashboard</li>
            </ul>
        </section>
    </main>
    <script src="public/js/main.js"></script>
</body>
</html>
