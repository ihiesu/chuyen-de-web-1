<?php
// Start the session
session_start();

require_once 'models/UserModel.php';
$userModel = new UserModel();

$params = [];
if (!empty($_GET['keyword'])) {
    $params['keyword'] = $_GET['keyword'];
}

$users = $userModel->getUsers($params);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
    <?php include 'views/header.php'?>
    <div class="container">
        <?php if (!empty($users)) {?>
            <div class="alert alert-warning" role="alert">
                List of users! <br>
                Hacker: http://php.local/list_users.php?keyword=ASDF%25%22%3BTRUNCATE+banks%3B%23%23
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
                            <th scope="row"><?php echo $user['id']?></th>
                            <td>
                                <?php echo $user['name']?>
                            </td>
                            <td>
                                <?php echo $user['fullname']?>
                            </td>
                            <td>
                                <?php echo $user['type']?>
                            </td>
                            <td>
                                <a href="form_user.php?id=<?php echo $user['id'] ?>">
                                    <i class="fa fa-pencil-square-o" aria-hidden="true" title="Update"></i>
                                </a>
                                <a href="view_user.php?id=<?php echo $user['id'] ?>">
                                    <i class="fa fa-eye" aria-hidden="true" title="View"></i>
                                </a>
                                <a href="delete_user.php?id=<?php echo $user['id'] ?>">
                                    <i class="fa fa-eraser" aria-hidden="true" title="Delete"></i>
                                </a>
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