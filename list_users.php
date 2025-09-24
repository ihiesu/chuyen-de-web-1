<?php
// list_users.php
if (session_status() === PHP_SESSION_NONE) {
session_set_cookie_params(['samesite' => 'Lax']);
session_start();
}


require_once 'models/UserModel.php';
$userModel = new UserModel();


$params = [];
if (!empty($_GET['keyword'])) {
$params['keyword'] = $_GET['keyword'];
}


$users = $userModel->getUsers($params);


// Ensure CSRF token for delete forms
if (empty($_SESSION['csrf_token'])) {
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<title>Home</title>
<?php include 'views/meta.php' ?>
</head>
<body>
<?php include 'views/header.php'?>
<div class="container">
<?php if (!empty($users)) {?>
<div class="alert alert-warning" role="alert">
List of users! <br>
<!-- Keep any debug link text but DO NOT use GET delete -->
</div>
<table class="table table-striped">
<thead>
<tr>
<th scope="col">ID</th>
<th scope="col">Username</th>
<th scope="col">Fullname</th>
<th scope="col">Type</th>
<th scope="col">Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($users as $user) {?>
<tr>
<th scope="row"><?php echo htmlspecialchars($user['id'])?></th>
<td><?php echo htmlspecialchars($user['name'])?></td>
<td><?php echo htmlspecialchars($user['fullname'])?></td>
<td><?php echo htmlspecialchars($user['type'])?></td>
<td>
<a href="form_user.php?id=<?php echo (int)$user['id']; ?>">
<i class="fa fa-pencil-square-o" aria-hidden="true" title="Update"></i>
</a>
<a href="view_user.php?id=<?php echo (int)$user['id']; ?>">
<i class="fa fa-eye" aria-hidden="true" title="View"></i>
</a>


<!-- Delete button uses POST with CSRF token -->
<form method="POST" action="delete_user.php" style="display:inline; margin:0; padding:0;">
<input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
<button type="submit" onclick="return confirm('Bạn có chắc muốn xóa user này?')" style="border:none;background:none;color:red;padding:0;">
<i class="fa fa-eraser" aria-hidden="true" title="Delete"></i>
</button>
</form>
</td>
</tr>
<?php } ?>
</tbody>
</table>
<?php }else { ?>
<div class="alert alert-dark" role="alert">
This is a dark alert—check it out!
</div>
<?php } ?>
</div>
</body>
<script>
function saveUserActivity(action) {
let logs = JSON.parse(localStorage.getItem("user_logs")) || [];


logs.push({
action: action,
time: new Date().toLocaleString()
});


localStorage.setItem("user_logs", JSON.stringify(logs));
}


// ===== 1. Log khi user vào trang =====
if (localStorage.getItem("user_id")) {
saveUserActivity("Truy cập trang: " + window.location.pathname + window.location.search);
}


// ===== 2. Log click vào mọi nút, link =====
document.addEventListener("click", function(e) {
if (e.target.tagName === "A" || e.target.tagName === "BUTTON") {
let text = e.target.innerText || e.target.getAttribute("title") || e.target.getAttribute("name") || "Nút không rõ";
saveUserActivity("Click vào: " + text);
}
});


// ===== 3. Log khi submit form =====
document.addEventListener("submit", function(e) {
let formName = e.target.getAttribute("id") || e.target.getAttribute("name") || "Form không rõ";
saveUserActivity("Submit form: " + formName);
});


// ===== 4. Log khi nhập dữ liệu input =====
document.addEventListener("change", function(e) {
if (e.target.tagName === "INPUT" || e.target.tagName === "SELECT" || e.target.tagName === "TEXTAREA") {
let fieldName = e.target.getAttribute("name") || e.target.getAttribute("id") || "Trường không rõ";
saveUserActivity("Thay đổi dữ liệu: " + fieldName + " = " + e.target.value);
}
});


// ===== 5. Log logout (xoá user_id khỏi localStorage) =====
function logout() {
saveUserActivity("Logout");
localStorage.removeItem("user_id");
localStorage.removeItem("username");
}


// Debug: in log ra console
console.log("=== Nhật ký hoạt động người dùng ===");
console.log(JSON.parse(localStorage.getItem("user_logs")) || []);
</script>


</html>