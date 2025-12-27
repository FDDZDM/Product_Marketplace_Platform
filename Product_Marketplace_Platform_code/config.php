<?php
// 数据库连接配置
$host = 'localhost:3308';
$user = 'root';
$pwd = ''; // 替换为你的MySQL密码
$dbname = 'try4_2023150020';

// 连接数据库
$conn = mysqli_connect($host, $user, $pwd, $dbname);
if (!$conn) {
    die("数据库连接失败: " . mysqli_connect_error());
}
// 设置字符集
mysqli_set_charset($conn, 'utf8');
?>