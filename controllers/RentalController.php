<?php

namespace Controllers;

use Models\Rental;

class RentalController extends BaseController
{
    private Rental $rentalModel;

    public function __construct()
    {
        parent::__construct();
        $this->rentalModel = new Rental($this->database);
    }

    public function index(): string
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form = $_POST['form'] ?? '';
            if ($form === 'property') {
                $this->rentalModel->createProperty($_POST);
            }

            if ($form === 'tenant') {
                $this->rentalModel->createTenant($_POST);
            }

            if ($form === 'contract') {
                $this->rentalModel->createContract($_POST);
            }

            if ($form === 'transaction') {
                $this->rentalModel->recordPayment($_POST);
            }

            header('Location: ?module=rental');
            exit;
        }

        $properties = $this->rentalModel->getProperties();
        $tenants = $this->rentalModel->getTenants();
        $contracts = $this->rentalModel->getContracts();
        $transactions = $this->rentalModel->getTransactions();
        $upcoming = $this->rentalModel->getUpcomingRent(5);
        $summary = $this->rentalModel->getSummary();

        return $this->render('rental/index.php', [
            'properties' => $properties,
            'tenants' => $tenants,
            'contracts' => $contracts,
            'transactions' => $transactions,
            'upcoming' => $upcoming,
            'summary' => $summary,
        ]);
    }
}
