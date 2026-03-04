<?php

namespace Controllers;

use Models\Reminder;

class ReminderController extends BaseController
{
    private Reminder $reminderModel;

    public function __construct()
    {
        parent::__construct();
        $this->reminderModel = new Reminder($this->database);
    }

    public function index(): string
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'reminder') {
            $this->reminderModel->create($_POST);
            header('Location: ?module=reminders');
            exit;
        }

        $upcoming = $this->reminderModel->getUpcoming(10);
        $total = $this->reminderModel->count();

        return $this->render('reminders/index.php', [
            'upcoming' => $upcoming,
            'total' => $total,
        ]);
    }
}
