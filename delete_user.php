<?php
// delete_user.php
if (session_status() === PHP_SESSION_NONE) {
session_set_cookie_params(['samesite' => 'Lax']);
session_start();
}
require_once 'models/UserModel.php';
$userModel = new UserModel();


// Only accept POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// CSRF check
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
// Invalid token
$_SESSION['message'] = 'Invalid CSRF token';
header('Location: list_users.php');
exit;
}


// Rotate token after use
unset($_SESSION['csrf_token']);


if (!empty($_POST['id'])) {
$id = (int)$_POST['id'];
$userModel->deleteUserById($id);//Delete existing user
}
}
header('location: list_users.php');
exit;
?>