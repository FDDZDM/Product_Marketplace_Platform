<?php
require_once '../common.php';
checkLogin();
if (!isAdmin()) showMsg('管理员功能！', '../index.php');

// 数据库前缀（适配你的新数据库结构）
$db_prefix = 'try4_2023150020.';

// 1. 冻结用户（原有逻辑）
if (isset($_GET['act']) && $_GET['act'] == 'freeze') {
    $uid = $_GET['uid'];
    mysqli_query($conn, "UPDATE {$db_prefix}user SET valid=0 WHERE uid=$uid");
    showMsg('用户已冻结！', 'user_freeze.php');
}

// 2. 新增：撤销冻结（恢复用户正常状态）
if (isset($_GET['act']) && $_GET['act'] == 'unfreeze') {
    $uid = $_GET['uid'];
    mysqli_query($conn, "UPDATE {$db_prefix}user SET valid=1 WHERE uid=$uid");
    showMsg('已撤销冻结，用户账号恢复正常！', 'user_freeze.php');
}

// 3. 查询所有普通用户（关联用户类型表）
$sql = "SELECT u.*, ut.utypename FROM {$db_prefix}user u JOIN {$db_prefix}usertype ut ON u.utypeid=ut.utypeid WHERE ut.utypename='普通用户'";
$users = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>用户管理</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container {width: 90%; max-width: 1000px; margin: 50px auto;}
        .table {width: 100%; border-collapse: collapse;}
        .table th, .table td {border: 1px solid #ddd; padding: 12px; text-align: center;}
        .table th {background: #f8f9fa; font-weight: 500;}
        .btn-freeze {color: #dc3545; text-decoration: none;}
        .btn-unfreeze {color: #28a745; text-decoration: none;}
        .status-normal {color: #28a745; font-weight: 500;}
        .status-frozen {color: #dc3545; font-weight: 500;}
        .btn-back {margin-top: 20px; display: inline-block; color: #007bff; text-decoration: none;}
    </style>
</head>
<body>
    <div class="container">
        <h1>用户管理（冻结/撤销违规用户）</h1>
        <table class="table">
            <tr>
                <th>用户ID</th>
                <th>用户名</th>
                <th>违规次数</th>
                <th>账号状态</th>
                <th>操作</th>
            </tr>
            <?php while ($u = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td><?= $u['uid'] ?></td>
                <td><?= $u['username'] ?></td>
                <td><?= $u['illegalcnt'] ?></td>
                <!-- 状态文字加样式区分 -->
                <td class="<?= $u['valid'] == 1 ? 'status-normal' : 'status-frozen' ?>">
                    <?= $u['valid'] == 1 ? '正常' : '已冻结' ?>
                </td>
                <td>
                    <?php if ($u['valid'] == 1): ?>
                        <!-- 账号正常：显示冻结按钮 -->
                        <a href="user_freeze.php?act=freeze&uid=<?= $u['uid'] ?>" 
                           class="btn-freeze" onclick="return confirm('确定冻结该用户账号吗？')">
                            冻结账号
                        </a>
                    <?php else: ?>
                        <!-- 账号冻结：显示撤销冻结按钮 -->
                        <a href="user_freeze.php?act=unfreeze&uid=<?= $u['uid'] ?>" 
                           class="btn-unfreeze" onclick="return confirm('确定撤销冻结，恢复该用户账号吗？')">
                            撤销冻结
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <a href="../index.php" class="btn-back">返回首页</a>
    </div>
</body>
</html>