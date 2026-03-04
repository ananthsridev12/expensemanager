<?php

namespace Controllers;

use Config\Database;

class BaseController
{
    protected Database $database;

    public function __construct()
    {
        $this->database = new Database();
    }
}
