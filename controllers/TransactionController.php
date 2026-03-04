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
            $this->handleTransaction($_POST);
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

    private function handleTransaction(array $input): void
    {
        $transactionType = $input['transaction_type'] ?? 'expense';
        $amount = is_numeric($input['amount'] ?? null) ? (float) $input['amount'] : 0.0;

        if ($transactionType === 'transfer') {
            $fromAccountId = !empty($input['account_id']) ? (int) $input['account_id'] : null;
            $toAccountId = !empty($input['transfer_to_account_id']) ? (int) $input['transfer_to_account_id'] : null;

            if ($fromAccountId && $toAccountId && $fromAccountId !== $toAccountId && $amount > 0) {
                $baseData = [
                    'transaction_date' => $input['transaction_date'] ?? date('Y-m-d'),
                    'category_id' => !empty($input['category_id']) ? (int) $input['category_id'] : null,
                    'subcategory_id' => !empty($input['subcategory_id']) ? (int) $input['subcategory_id'] : null,
                    'notes' => $input['notes'] ?? 'Account transfer',
                    'reference_type' => 'transfer',
                ];

                $this->transactionModel->create(array_merge($baseData, [
                    'account_type' => $input['account_type'] ?? 'bank',
                    'account_id' => $fromAccountId,
                    'transaction_type' => 'expense',
                    'amount' => $amount,
                    'reference_id' => $toAccountId,
                ]));

                $this->transactionModel->create(array_merge($baseData, [
                    'account_type' => $input['account_type'] ?? 'bank',
                    'account_id' => $toAccountId,
                    'transaction_type' => 'income',
                    'amount' => $amount,
                    'reference_id' => $fromAccountId,
                    'notes' => 'Transfer from account ' . $fromAccountId,
                ]));
            }
        } else {
            $this->transactionModel->create($input);
        }
    }
}
