<?php

require_once __DIR__ . '/autoload.php';

use Controllers\AccountController;
use Controllers\CategoryController;
use Controllers\CreditCardController;
use Controllers\DashboardController;
use Controllers\InvestmentController;
use Controllers\LoanController;
use Controllers\LendingController;
use Controllers\ReminderController;
use Controllers\RentalController;
use Controllers\SipController;
use Controllers\TransactionController;

$module = filter_input(INPUT_GET, 'module', FILTER_SANITIZE_STRING) ?: 'dashboard';

switch ($module) {
    case 'accounts':
        $controller = new AccountController();
        echo $controller->index();
        break;
    case 'categories':
        $controller = new CategoryController();
        echo $controller->index();
        break;
    case 'transactions':
        $controller = new TransactionController();
        echo $controller->index();
        break;
    case 'credit_cards':
        $controller = new CreditCardController();
        echo $controller->index();
        break;
    case 'reminders':
        $controller = new ReminderController();
        echo $controller->index();
        break;
    case 'loans':
        $controller = new LoanController();
        echo $controller->index();
        break;
    case 'lending':
        $controller = new LendingController();
        echo $controller->index();
        break;
    case 'investments':
        $controller = new InvestmentController();
        echo $controller->index();
        break;
    case 'sip':
        $controller = new SipController();
        echo $controller->index();
        break;
    case 'rental':
        $controller = new RentalController();
        echo $controller->index();
        break;
    case 'dashboard':
    default:
        $controller = new DashboardController();
        echo $controller->index();
        break;
}
