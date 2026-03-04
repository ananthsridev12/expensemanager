<?php

namespace Controllers;

use Models\CreditCard;

class CreditCardController extends BaseController
{
    private CreditCard $creditCardModel;

    public function __construct()
    {
        parent::__construct();
        $this->creditCardModel = new CreditCard($this->database);
    }

    public function index(): string
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'credit_card') {
            $this->creditCardModel->create($_POST);
            header('Location: ?module=credit_cards');
            exit;
        }

        $cards = $this->creditCardModel->getAll();
        $summary = $this->creditCardModel->getSummary();

        return $this->render('credit_cards/index.php', [
            'cards' => $cards,
            'summary' => $summary,
        ]);
    }
}
