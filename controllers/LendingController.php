<?php

namespace Controllers;

use Models\Lending;

class LendingController extends BaseController
{
    private Lending $lendingModel;

    public function __construct()
    {
        parent::__construct();
        $this->lendingModel = new Lending($this->database);
    }

    public function index(): string
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'lending') {
            $this->lendingModel->create($_POST);
            header('Location: ?module=lending');
            exit;
        }

        $records = $this->lendingModel->getAll();
        $summary = $this->lendingModel->getSummary();

        return $this->render('lending/index.php', [
            'records' => $records,
            'summary' => $summary,
        ]);
    }
}
