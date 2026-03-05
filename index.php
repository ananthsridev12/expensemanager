<?php

require_once __DIR__ . '/autoload.php';

use Controllers\AccountController;
use Controllers\AnalyticsController;
use Controllers\CategoryController;
use Controllers\ContactController;
use Controllers\CreditCardController;
use Controllers\DashboardController;
use Controllers\InvestmentController;
use Controllers\LoanController;
use Controllers\LendingController;
use Controllers\ReminderController;
use Controllers\RentalController;
use Controllers\SipController;
use Controllers\TransactionController;

$moduleInput = filter_input(INPUT_GET, 'module', FILTER_DEFAULT);
$module = is_string($moduleInput) ? preg_replace('/[^a-z_]/i', '', $moduleInput) : 'dashboard';
$module = $module !== '' ? $module : 'dashboard';

switch ($module) {
    case 'accounts':
        $controller = new AccountController();
        echo $controller->index();
        break;
    case 'analytics':
        $controller = new AnalyticsController();
        echo $controller->index();
        break;
    case 'categories':
        $controller = new CategoryController();
        echo $controller->index();
        break;
    case 'contacts':
        $controller = new ContactController();
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
