<?php

namespace Controllers;

use Models\Contact;

class ContactController extends BaseController
{
    private Contact $contactModel;

    public function __construct()
    {
        parent::__construct();
        $this->contactModel = new Contact($this->database);
    }

    public function index(): string
    {
        $action = $_GET['action'] ?? '';
        if ($action === 'search') {
            return $this->search();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'contact') {
            $this->contactModel->create($_POST);
            header('Location: ?module=contacts');
            exit;
        }

        $contacts = $this->contactModel->getAll();
        return $this->render('contacts/index.php', [
            'contacts' => $contacts,
        ]);
    }

    private function search(): string
    {
        $query = (string) ($_GET['q'] ?? '');
        $results = $this->contactModel->search($query, 20);
        header('Content-Type: application/json');
        return json_encode($results, JSON_THROW_ON_ERROR);
    }
}
