<?php

namespace Controllers;

use Models\Account;
use Models\Category;
use Models\CreditCard;
use Models\Loan;
use Models\Transaction;

class TransactionController extends BaseController
{
    private Transaction $transactionModel;
    private Account $accountModel;
    private Category $categoryModel;
    private CreditCard $creditCardModel;
    private Loan $loanModel;

    public function __construct()
    {
        parent::__construct();
        $this->transactionModel = new Transaction($this->database);
        $this->accountModel = new Account($this->database);
        $this->categoryModel = new Category($this->database);
        $this->creditCardModel = new CreditCard($this->database);
        $this->loanModel = new Loan($this->database);
    }

    public function index(): string
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'transaction') {
            $this->handleTransaction($_POST);
            header('Location: ?module=transactions');
            exit;
        }

        $accounts = $this->accountModel->getList();
        $loans = $this->loanModel->getAll();
        $categories = $this->categoryModel->getAllWithSubcategories();
        $recentTransactions = $this->transactionModel->getRecent(15);
        $totalsByType = $this->transactionModel->getTotalsByType();

        return $this->render('transactions/index.php', [
            'accounts' => $accounts,
            'loans' => $loans,
            'categories' => $categories,
            'recentTransactions' => $recentTransactions,
            'totalsByType' => $totalsByType,
        ]);
    }

    private function handleTransaction(array $input): void
    {
        $transactionType = $input['transaction_type'] ?? 'expense';
        $amount = is_numeric($input['amount'] ?? null) ? (float) $input['amount'] : 0.0;
        [$fromType, $fromId] = $this->parseAccountToken($input['account_id'] ?? '');

        if ($amount <= 0 || $fromId <= 0) {
            return;
        }

        if ($transactionType === 'transfer') {
            [$toType, $toId] = $this->parseAccountToken($input['transfer_to_account_id'] ?? '');

            if ($toId > 0 && !($fromType === $toType && $fromId === $toId)) {
                $baseData = [
                    'transaction_date' => $input['transaction_date'] ?? date('Y-m-d'),
                    'category_id' => !empty($input['category_id']) ? (int) $input['category_id'] : null,
                    'subcategory_id' => !empty($input['subcategory_id']) ? (int) $input['subcategory_id'] : null,
                    'notes' => $input['notes'] ?? 'Account transfer',
                    'reference_type' => 'transfer',
                ];

                $this->transactionModel->create(array_merge($baseData, [
                    'account_type' => $fromType,
                    'account_id' => $this->resolveTransactionAccountId($fromType, $fromId),
                    'transaction_type' => 'expense',
                    'amount' => $amount,
                    'reference_type' => $this->resolveReferenceType($fromType, 'transfer'),
                    'reference_id' => $this->resolveReferenceId($fromType, $fromId, $toId),
                ]));
                $this->applyDebtDelta($fromType, $fromId, 'expense', $amount);

                $this->transactionModel->create(array_merge($baseData, [
                    'account_type' => $toType,
                    'account_id' => $this->resolveTransactionAccountId($toType, $toId),
                    'transaction_type' => 'income',
                    'amount' => $amount,
                    'reference_type' => $this->resolveReferenceType($toType, 'transfer'),
                    'reference_id' => $this->resolveReferenceId($toType, $toId, $fromId),
                    'notes' => 'Transfer from account ' . $fromId,
                ]));
                $this->applyDebtDelta($toType, $toId, 'income', $amount);
            }
        } else {
            $isEmiPurchase = ($input['is_emi_purchase'] ?? 'no') === 'yes';
            $isCreditCardEmiExpense = $fromType === 'credit_card'
                && $transactionType === 'expense'
                && $isEmiPurchase
                && !empty($input['emi_name'])
                && !empty($input['emi_date'])
                && !empty($input['total_emis']);

            if ($isCreditCardEmiExpense) {
                $emiResult = $this->creditCardModel->createEmiPlanFromTransaction([
                    'account_id' => $fromId,
                    'plan_name' => $input['emi_name'] ?? '',
                    'principal_amount' => $amount,
                    'interest_rate' => $input['interest_rate'] ?? 0,
                    'total_emis' => $input['total_emis'] ?? 1,
                    'emi_date' => $input['emi_date'] ?? null,
                    'processing_fee' => $input['processing_fee'] ?? 0,
                    'gst_rate' => $input['gst_rate'] ?? 0,
                    'transaction_date' => $input['transaction_date'] ?? date('Y-m-d'),
                    'notes' => $input['notes'] ?? null,
                ]);

                if (!($emiResult['success'] ?? false)) {
                    return;
                }

                $this->transactionModel->create(array_merge($input, [
                    'account_type' => 'credit_card',
                    'account_id' => $fromId,
                    'transaction_type' => 'expense',
                    'reference_type' => 'credit_card_emi_plan',
                    'reference_id' => (int) ($emiResult['plan_id'] ?? 0),
                ]));
                return;
            }

            $this->transactionModel->create(array_merge($input, [
                'account_type' => $fromType,
                'account_id' => $this->resolveTransactionAccountId($fromType, $fromId),
                'reference_type' => $this->resolveReferenceType($fromType, $input['reference_type'] ?? null),
                'reference_id' => $this->resolveReferenceId($fromType, $fromId, !empty($input['reference_id']) ? (int) $input['reference_id'] : null),
            ]));
            $this->applyDebtDelta($fromType, $fromId, $transactionType, $amount);
        }
    }

    private function parseAccountToken(string $token): array
    {
        if (strpos($token, ':') === false) {
            return ['savings', (int) $token];
        }

        [$type, $id] = explode(':', $token, 2);
        $allowedTypes = ['savings', 'current', 'credit_card', 'cash', 'other', 'loan'];
        $normalizedType = in_array($type, $allowedTypes, true) ? $type : 'savings';
        return [$normalizedType, (int) $id];
    }

    private function applyDebtDelta(string $accountType, int $accountId, string $transactionType, float $amount): void
    {
        if ($accountType === 'credit_card') {
            $this->creditCardModel->applyTransactionMovementByAccount($accountId, $transactionType, $amount);
            return;
        }

        if ($accountType === 'loan') {
            $this->loanModel->applyTransactionMovement($accountId, $transactionType, $amount);
        }
    }

    private function resolveTransactionAccountId(string $accountType, int $accountId): ?int
    {
        if ($accountType === 'loan') {
            return null;
        }

        return $accountId > 0 ? $accountId : null;
    }

    private function resolveReferenceType(string $accountType, ?string $defaultType): ?string
    {
        if ($accountType === 'loan') {
            return 'loan';
        }

        return $defaultType;
    }

    private function resolveReferenceId(string $accountType, int $accountId, ?int $fallbackId): ?int
    {
        if ($accountType === 'loan') {
            return $accountId > 0 ? $accountId : null;
        }

        return $fallbackId;
    }
}
