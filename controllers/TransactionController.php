<?php

namespace Controllers;

use Models\Account;
use Models\Category;
use Models\Transaction;

class TransactionController extends BaseController
{
    private Transaction $transactionModel;
    private Account $accountModel;
    private Category $categoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->transactionModel = new Transaction($this->database);
        $this->accountModel = new Account($this->database);
        $this->categoryModel = new Category($this->database);
    }

    public function index(): string
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'transaction') {
            $this->transactionModel->create($_POST);
            header('Location: ?module=transactions');
            exit;
        }

        $accounts = $this->accountModel->getList();
        $categories = $this->categoryModel->getAllWithSubcategories();
        $recentTransactions = $this->transactionModel->getRecent(15);
        $totalsByType = $this->transactionModel->getTotalsByType();

        return $this->render('transactions/index.php', [
            'accounts' => $accounts,
            'categories' => $categories,
            'recentTransactions' => $recentTransactions,
            'totalsByType' => $totalsByType,
        ]);
    }
}
