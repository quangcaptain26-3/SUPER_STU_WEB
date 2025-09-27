<?php
session_start();
require_once '../authController.php';

$auth = new AuthController();
$auth->logout();

header('Location: login.php?message=logout_success');
exit();
?>
