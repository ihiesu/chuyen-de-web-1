<?php
// form_user.php
if (session_status() === PHP_SESSION_NONE) {
session_set_cookie_params(['samesite' => 'Lax']);
session_start();
}


require_once 'models/UserModel.php';
$userModel = new UserModel();


// Ensure CSRF token
if (empty($_SESSION['csrf_token'])) {
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];


$user = NULL; // user mặc định
$_id = NULL;


if (!empty($_GET['id'])) {
$_id = (int)$_GET['id'];
$user = $userModel->findUserById($_id); // Update user
}


if (!empty($_POST['submit'])) {
// CSRF check
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
die('Invalid CSRF token');
}


// Rotate token after use
unset($_SESSION['csrf_token']);


if (!empty($_id)) {
// Update
// sanitize and pass required fields only
$data = [
'id' => $_POST['id'] ?? null,
'name' => $_POST['name'] ?? '',
'fullname' => $_POST['fullname'] ?? '',
'email' => $_POST['email'] ?? '',
'type' => $_POST['type'] ?? '',
'password' => $_POST['password'] ?? ''
];
$userModel->updateUser($data);
} else {
// Insert
$data = [
'name' => $_POST['name'] ?? '',
'fullname' => $_POST['fullname'] ?? '',
'email' => $_POST['email'] ?? '',
'type' => $_POST['type'] ?? '',
'password' => $_POST['password'] ?? ''
];
$userModel->insertUser($data);
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
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
<input type="hidden" name="id" value="<?php echo htmlspecialchars($_id); ?>">


<div class="form-group">
<label for="name">Username</label>
<input class="form-control" name="name" placeholder="Username"
value="<?php if (!empty($user[0]['name'])) echo htmlspecialchars($user[0]['name']); ?>">
</div>


<div class="form-group">
<label for="fullname">Fullname</label>
<input class="form-control" name="fullname" placeholder="Fullname"
value="<?php if (!empty($user[0]['fullname'])) echo htmlspecialchars($user[0]['fullname']); ?>">
</div>


<div class="form-group">
<label for="email">Email</label>
<input class="form-control" name="email" placeholder="Email"
value="<?php if (!empty($user[0]['email'])) echo htmlspecialchars($user[0]['email']); ?>">
</div>


<div class="form-group">
<label for="type">Type</label>
<input class="form-control" name="type" placeholder="User Type"
value="<?php if (!empty($user[0]['type'])) echo htmlspecialchars($user[0]['type']); ?>">
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