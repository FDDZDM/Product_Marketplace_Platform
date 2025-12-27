<?php
session_start();
require_once 'config.php';

// 验证是否登录
function checkLogin() {
    if (!isset($_SESSION['uid']) || !isset($_SESSION['username'])) {
        header('Location: login.php');
        exit;
    }
}

// 判断是否为管理员
function isAdmin() {
    $uid = $_SESSION['uid'];
    $sql = "SELECT ut.utypename FROM user u JOIN usertype ut ON u.utypeid=ut.utypeid WHERE u.uid=$uid";
    $res = mysqli_query($GLOBALS['conn'], $sql);
    $row = mysqli_fetch_assoc($res);
    return $row['utypename'] == '管理员';
}

// 提示信息函数
function showMsg($msg, $url) {
    echo "<script>alert('$msg'); location.href='$url';</script>";
    exit;
}
?>