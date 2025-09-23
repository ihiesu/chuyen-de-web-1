<?php
// Start the session
session_start();

require_once 'models/UserModel.php';
$userModel = new UserModel();

if (!empty($_POST['submit'])) {
    $name = $_POST['name'] ?? '';
    $password = $_POST['password'] ?? '';

    // Kiểm tra đăng nhập
    $user = $userModel->auth($name, $password);

    if (!empty($user)) {
        // Login successful
        $_SESSION['id'] = $user[0]['id'];
        $_SESSION['message'] = 'Login successful';

        // Xuất JavaScript để lưu vào localStorage rồi redirect
        echo "<script>
            localStorage.setItem('user_id', '" . $user[0]['id'] . "');
            localStorage.setItem('username', '" . $user[0]['name'] . "');
            alert('Đăng nhập thành công!');
            window.location.href = 'list_users.php';
        </script>";
        exit;
    } else {
        // Login failed
        $_SESSION['message'] = 'Login failed';
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
                    <div class="alert alert-warning"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                <?php } ?>
                <form method="post" class="form-horizontal" role="form">

                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input id="login-username" type="text" class="form-control" name="name" placeholder="Username">
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input id="login-password" type="password" class="form-control" name="password" placeholder="Password">
                    </div>

                    <div class="margin-bottom-25">
                        <input type="checkbox" tabindex="3" class="" name="remember" id="remember">
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
