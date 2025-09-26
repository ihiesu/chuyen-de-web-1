<?php
// delete_user.php
require_once __DIR__ . '/models/UserModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['samesite' => 'Lax']);
    session_start();
}

$userModel = new UserModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $_SESSION['message'] = 'Invalid CSRF token';
        header('Location: list_users.php');
        exit;
    }

    // rotate token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    if (!empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        try {
            $userModel->deleteUserById($id);
        } catch (Exception $e) {
            $_SESSION['message'] = 'Lỗi khi xóa user';
        }
    }
}

header('Location: list_users.php');
exit;
