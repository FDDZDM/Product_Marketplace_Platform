<?php
require_once 'common.php'; // 替换原来的 config.php
if ($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password']; // 实际项目需加密，此处简化
    $utypename = $_POST['utypename'];

    // 检查用户名是否重复
    $sql = "SELECT * FROM user WHERE username='$username'";
    $res = mysqli_query($conn, $sql);
    if (mysqli_num_rows($res) > 0) {
        showMsg('用户名已存在！', 'register.php');
    }

    // 获取用户类型ID（1=管理员，2=普通用户）
    $utypeid = $utypename == 'admin' ? 1 : 2;

    // 插入用户（初始违规次数0，账号有效）
    $sql = "INSERT INTO user (utypeid, username, password, illegalcnt, valid) 
            VALUES ($utypeid, '$username', '$password', 0, 1)";
    if (mysqli_query($conn, $sql)) {
        showMsg('注册成功！请登录', 'login.php');
    } else {
        showMsg('注册失败：' . mysqli_error($conn), 'register.php');
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>用户注册</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>用户注册</h1>
        <form method="post">
            <div class="form-group">
                <label>用户名</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>密码</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>用户类型</label>
                <select name="utypename" required>
                    <option value="user">普通用户</option>
                    <option value="admin">管理员</option>
                </select>
            </div>
            <button type="submit">注册</button>
            <p style="margin-top:10px;">已有账号？<a href="login.php">立即登录</a></p>
        </form>
    </div>
</body>
</html>