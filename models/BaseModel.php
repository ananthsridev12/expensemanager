<?php

namespace Models;

use Config\Database;
use PDO;

class BaseModel
{
    protected PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->connect();
    }
}
