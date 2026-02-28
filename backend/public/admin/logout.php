<?php
require __DIR__ . '/_bootstrap.php';
unset($_SESSION['admin_user']);
session_destroy();
header('Location: login.php');
exit;
