<?php

namespace Controllers;

use Models\Account;
use Models\Category;
use Models\CreditCard;
use Models\Investment;
use Models\Loan;
use Models\Lending;
use Models\Reminder;
use Models\Rental;
use Models\Transaction;

class DashboardController extends BaseController
{
    private Account $accountModel;
    private Category $categoryModel;
    private CreditCard $creditCardModel;
    private Investment $investmentModel;
    private Loan $loanModel;
    private Lending $lendingModel;
    private Reminder $reminderModel;
    private Rental $rentalModel;
    private Transaction $transactionModel;

    public function __construct()
    {
        parent::__construct();
        $this->accountModel = new Account($this->database);
        $this->categoryModel = new Category($this->database);
        $this->creditCardModel = new CreditCard($this->database);
        $this->investmentModel = new Investment($this->database);
        $this->loanModel = new Loan($this->database);
        $this->lendingModel = new Lending($this->database);
        $this->reminderModel = new Reminder($this->database);
        $this->rentalModel = new Rental($this->database);
        $this->transactionModel = new Transaction($this->database);
    }

    public function index(): string
    {
        $accountsSummary = $this->accountModel->getSummary();
        $loans = $this->loanModel->getAll();
        $summary = [
            'accounts' => $accountsSummary,
            'categories' => $this->categoryModel->count(),
            'transactions' => $this->transactionModel->countAll(),
            'reminders' => $this->reminderModel->count(),
            'loans' => [
                'count' => count($loans),
                'principal' => array_sum(array_column($loans, 'principal_amount')),
            ],
            'credit_cards' => $this->creditCardModel->getSummary(),
            'lending' => $this->lendingModel->getSummary(),
            'investments' => $this->investmentModel->getSummary(),
            'rentals' => $this->rentalModel->getSummary(),
        ];

        $recentTransactions = $this->transactionModel->getRecent(5);
        $upcomingReminders = $this->reminderModel->getUpcoming(3);
        $upcomingEmis = $this->loanModel->getUpcomingEmis(5);

        ob_start();
        include __DIR__ . '/../views/dashboard.php';
        return ob_get_clean();
    }
}
