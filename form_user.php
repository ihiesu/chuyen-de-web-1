<?php
// Start the session
session_start();
require_once 'models/UserModel.php';
$userModel = new UserModel();

$user = NULL; // user mặc định
$_id = NULL;

if (!empty($_GET['id'])) {
    $_id = $_GET['id'];
    $user = $userModel->findUserById($_id); // Update user
}

if (!empty($_POST['submit'])) {
    if (!empty($_id)) {
        // Update
        $userModel->updateUser($_POST);
    } else {
        // Insert
        $userModel->insertUser($_POST);
    }
    header('location: list_users.php');
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
            <div class="alert alert-warning" role="alert">
                User form
            </div>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $_id ?>">

                <div class="form-group">
                    <label for="name">Username</label>
                    <input class="form-control" name="name" placeholder="Username"
                           value="<?php if (!empty($user[0]['name'])) echo $user[0]['name'] ?>">
                </div>

                <div class="form-group">
                    <label for="fullname">Fullname</label>
                    <input class="form-control" name="fullname" placeholder="Fullname"
                           value="<?php if (!empty($user[0]['fullname'])) echo $user[0]['fullname'] ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input class="form-control" name="email" placeholder="Email"
                           value="<?php if (!empty($user[0]['email'])) echo $user[0]['email'] ?>">
                </div>

                <div class="form-group">
                    <label for="type">Type</label>
                    <input class="form-control" name="type" placeholder="User Type"
                           value="<?php if (!empty($user[0]['type'])) echo $user[0]['type'] ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" name="password" placeholder="Password">
                </div>

                <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
            </form>
        <?php } else { ?>
            <div class="alert alert-danger" role="alert">
                User not found!
            </div>
        <?php } ?>
    </div>
</body>
</html>
