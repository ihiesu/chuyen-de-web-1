<?php
// login.php
require_once __DIR__ . '/models/UserModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

$userModel = new UserModel();

// ensure CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['submit'])) {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = 'Invalid CSRF token';
        header('Location: login.php');
        exit;
    }

    // rotate token safely (create new one)
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $csrf_token = $_SESSION['csrf_token'];

    $name = trim((string)($_POST['name'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($name === '' || $password === '') {
        $_SESSION['message'] = 'Username và password không được để trống';
        header('Location: login.php');
        exit;
    }

    try {
        $user = $userModel->auth($name, $password);
    } catch (Exception $e) {
        // in production chỉ log, không hiển thị chi tiết
        $_SESSION['message'] = 'Lỗi nội bộ';
        header('Location: login.php');
        exit;
    }

    if (!empty($user)) {
        // login thành công
        session_regenerate_id(true);
        $_SESSION['id'] = $user['id'];
        $_SESSION['username'] = $user['name'];
        $_SESSION['message'] = 'Đăng nhập thành công';

        // An toàn: dùng json_encode để tránh XSS khi gán vào localStorage
        $jsUserId = json_encode((string)$user['id']);
        $jsUserName = json_encode((string)$user['name']);

        echo "<script>
            try {
                localStorage.setItem('user_id', {$jsUserId});
                localStorage.setItem('username', {$jsUserName});
            } catch(e) {}
            alert('Đăng nhập thành công!');
            window.location.href = 'list_users.php';
        </script>";
        exit;
    } else {
        $_SESSION['message'] = 'Login failed';
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<?php include 'views/meta.php' ?>
</head>
<body>
<?php include 'views/header.php' ?>

<div class="container">
    <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="panel-title">Login</div>
                <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot password?</a></div>
            </div>

            <div style="padding-top:30px" class="panel-body">
                <?php if (!empty($_SESSION['message'])) { ?>
                    <div class="alert alert-warning"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
                <?php } ?>
                <form method="post" class="form-horizontal" role="form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input id="login-username" type="text" class="form-control" name="name" placeholder="Username or Email" required>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input id="login-password" type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>

                    <div class="margin-bottom-25">
                        <input type="checkbox" tabindex="3" name="remember" id="remember">
                        <label for="remember"> Remember Me</label>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <div class="col-sm-12 controls">
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">Login</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12 control">
                            Don't have an account?
                            <a href="form_user.php">Sign Up Here</a>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

</body>
</html>
