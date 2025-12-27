<?php
require_once '../common.php';
checkLogin();
if (!isAdmin()) showMsg('管理员功能！', '../index.php');

// 数据库前缀（适配新数据库）
$db_prefix = 'try4_2023150020.';

// 1. 下架违规产品
if (isset($_GET['act']) && $_GET['act'] == 'drop') {
    $pid = $_GET['pid'];
    $reason = $_GET['reason'] ?? '违规宣传'; // 兜底默认原因
    mysqli_query($conn, "UPDATE {$db_prefix}product SET pstate='违规下架', dropreason='$reason' WHERE pid=$pid");
    showMsg('产品已下架！', 'product_manage.php');
}

// 2. 新增：撤销下架（恢复上架）
if (isset($_GET['act']) && $_GET['act'] == 'restore') {
    $pid = $_GET['pid'];
    // 恢复为上架状态，清空下架原因
    mysqli_query($conn, "UPDATE {$db_prefix}product SET pstate='上架', dropreason='' WHERE pid=$pid");
    showMsg('已撤销下架，产品恢复上架！', 'product_manage.php');
}

// 3. 查询所有产品（关联产品类型表）
$sql = "SELECT p.*, pt.ptype FROM {$db_prefix}product p JOIN {$db_prefix}producttype pt ON p.ptypeid=pt.ptypeid where p.pstate = '上架'";
$products = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>产品管理</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container {width: 90%; max-width: 1000px; margin: 50px auto;}
        .table {width: 100%; border-collapse: collapse;}
        .table th, .table td {border: 1px solid #ddd; padding: 10px; text-align: center;}
        .table th {background: #f8f9fa;}
        .btn-drop {color: #dc3545; text-decoration: none;}
        .btn-restore {color: #28a745; text-decoration: none;}
        .btn-back {margin-top: 20px; display: inline-block; color: #007bff; text-decoration: none;}
    </style>
</head>
<body>
    <div class="container">
        <h1>产品管理（下架/撤销违规产品）</h1>
        <table class="table">
            <tr>
                <th>产品ID</th>
                <th>产品名</th>
                <th>产品类型</th>
                <th>状态</th>
                <th>下架原因</th>
                <th>操作</th>
            </tr>
            <?php while ($p = mysqli_fetch_assoc($products)): ?>
            <tr>
                <td><?= $p['pid'] ?></td>
                <td><?= $p['pname'] ?></td>
                <td><?= $p['ptype'] ?></td>
                <td><?= $p['pstate'] ?></td>
                <td><?= $p['dropreason'] ?? '-' ?></td>
                <td>
                    <?php if ($p['pstate'] != '违规下架'): ?>
                        <!-- 未下架：显示下架按钮 -->
                        <a href="product_manage.php?act=drop&pid=<?= $p['pid'] ?>&reason=违规" 
                           class="btn-drop" onclick="return confirm('确定下架该产品吗？')">
                            下架（违规）
                        </a>
                    <?php else: ?>
                        <!-- 已下架：显示撤销按钮 -->
                        <a href="product_manage.php?act=restore&pid=<?= $p['pid'] ?>" 
                           class="btn-restore" onclick="return confirm('确定撤销下架，恢复上架吗？')">
                            撤销下架
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