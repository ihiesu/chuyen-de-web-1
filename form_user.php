<?php
// form_user.php
require_once __DIR__ . '/models/UserModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['samesite' => 'Lax']);
    session_start();
}

$userModel = new UserModel();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$user = null;
$_id = null;

if (!empty($_GET['id'])) {
    $_id = (int)$_GET['id'];
    $user = $userModel->getUserByIdSingle($_id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['submit'])) {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    // rotate token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $csrf_token = $_SESSION['csrf_token'];

    // Lấy dữ liệu từ POST (sanitize cơ bản)
    $data = [
        'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
        'name' => trim((string)($_POST['name'] ?? '')),
        'fullname' => trim((string)($_POST['fullname'] ?? '')),
        'email' => trim((string)($_POST['email'] ?? '')),
        'type' => trim((string)($_POST['type'] ?? '')),
        'password' => (string)($_POST['password'] ?? '')
    ];

    try {
        if (!empty($data['id'])) {
            $userModel->updateUser($data);
        } else {
            $userModel->insertUser($data);
        }
    } catch (Exception $e) {
        $_SESSION['message'] = 'Lỗi khi lưu dữ liệu người dùng';
        header('Location: form_user.php' . (!empty($data['id']) ? '?id=' . (int)$data['id'] : ''));
        exit;
    }

    header('Location: list_users.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>User form</title>
<?php include 'views/meta.php' ?>
</head>
<body>
<?php include 'views/header.php' ?>
<div class="container">
    <?php if ($user || !isset($_id)) { ?>
        <div class="alert alert-warning" role="alert">User form</div>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($_id); ?>">

            <div class="form-group">
                <label for="name">Username</label>
                <input class="form-control" name="name" placeholder="Username"
                    value="<?php echo !empty($user['name']) ? htmlspecialchars($user['name']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="fullname">Fullname</label>
                <input class="form-control" name="fullname" placeholder="Fullname"
                    value="<?php echo !empty($user['fullname']) ? htmlspecialchars($user['fullname']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input class="form-control" name="email" placeholder="Email"
                    value="<?php echo !empty($user['email']) ? htmlspecialchars($user['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="type">Type</label>
                <input class="form-control" name="type" placeholder="User Type"
                    value="<?php echo !empty($user['type']) ? htmlspecialchars($user['type']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" placeholder="Password">
                <small class="form-text text-muted">Nếu để trống sẽ giữ nguyên mật khẩu cũ (khi cập nhật).</small>
            </div>

            <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
        </form>
    <?php } else { ?>
        <div class="alert alert-danger" role="alert">User not found!</div>
    <?php } ?>
</div>
</body>
</html>
