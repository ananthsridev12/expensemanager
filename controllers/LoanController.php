<?php

namespace Controllers;

use Models\Account;
use Models\Loan;

class LoanController extends BaseController
{
    private Loan $loanModel;
    private Account $accountModel;

    public function __construct()
    {
        parent::__construct();
        $this->loanModel = new Loan($this->database);
        $this->accountModel = new Account($this->database);
    }

    public function index(): string
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'loan') {
            $this->loanModel->create($_POST);
            header('Location: ?module=loans');
            exit;
        }

        $loans = $this->loanModel->getAll();
        $accounts = array_values(array_filter(
            $this->accountModel->getList(),
            static fn (array $account): bool => ($account['account_type'] ?? '') !== 'credit_card'
        ));
        $upcomingEmis = $this->loanModel->getUpcomingEmis(8);
        $summary = [
            'count' => count($loans),
            'total_principal' => array_sum(array_column($loans, 'principal_amount')),
        ];

        return $this->render('loans/index.php', [
            'loans' => $loans,
            'accounts' => $accounts,
            'upcomingEmis' => $upcomingEmis,
            'summary' => $summary,
        ]);
    }
}
