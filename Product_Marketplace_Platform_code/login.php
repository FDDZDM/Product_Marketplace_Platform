<?php
require_once 'common.php'; // 替换原来的 config.php，common.php 已包含 config 且有 showMsg 函数
if ($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 验证用户
    $sql = "SELECT * FROM user WHERE username='$username' AND password='$password'";
    $res = mysqli_query($conn, $sql);
    if (mysqli_num_rows($res) == 0) {
        showMsg('用户名或密码错误！', 'login.php');
    }

    $user = mysqli_fetch_assoc($res);
    if ($user['valid'] == 0) {
        showMsg('账号已被冻结！', 'login.php');
    }

    // 保存登录状态
    $_SESSION['uid'] = $user['uid'];
    $_SESSION['username'] = $user['username'];
    showMsg('登录成功！', 'index.php');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>用户登录</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>用户登录</h1>
        <form method="post">
            <div class="form-group">
                <label>用户名</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>密码</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">登录</button>
            <p style="margin-top:10px;">没有账号？<a href="register.php">立即注册</a></p>
        </form>
    </div>
</body>
</html>