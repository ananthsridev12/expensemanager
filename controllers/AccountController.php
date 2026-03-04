<?php

namespace Controllers;

use Models\Account;

class AccountController extends BaseController
{
    private Account $accountModel;

    public function __construct()
    {
        parent::__construct();
        $this->accountModel = new Account($this->database);
    }

    public function index(): string
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'account') {
            $this->accountModel->create($_POST);
            header('Location: ?module=accounts');
            exit;
        }

        $accounts = $this->accountModel->getAllWithBalances();
        $summary = $this->accountModel->getSummary();

        ob_start();
        include __DIR__ . '/../views/accounts/index.php';
        return ob_get_clean();
    }
}
