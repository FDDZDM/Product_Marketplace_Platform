<?php
require_once 'common.php';
checkLogin();
// 查询所有上架产品
$sql = "SELECT p.*, pt.ptype FROM product p JOIN producttype pt ON p.ptypeid=pt.ptypeid WHERE p.pstate='上架' and p.stock>0 ";

$products = mysqli_query($conn, $sql);
$sql = "update product set pstate='自主下架' where stock<=0";
mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>特色时令产品平台</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="nav">
            <h2>欢迎：<?= $_SESSION['username'] ?></h2>
            <?php if (!isAdmin()): ?>
                <a href="user/product_publish.php">发布产品</a>
                <a href="user/buycar.php">我的购物车</a>
                <a href="user/complaint.php">提交投诉</a>
                <a href="user/user_product_manage.php">我的上架商品</a>
            <?php else: ?>
                <a href="admin/product_manage.php">产品管理</a>
                <a href="admin/complaint_handle.php">处理投诉</a>
                <a href="admin/user_freeze.php">冻结用户</a>
            <?php endif; ?>
            <a href="login.php" onclick="return confirm('确定退出？')">退出登录</a>
        </div>

        <h1>时令产品列表</h1>
        <table class="table">
            <tr>
                <th>产品ID</th>
                <th>产品名</th>
                <th>类别</th>
                <th>产地</th>
                <th>价格</th>
                <th>库存</th>
                <th>操作</th>
            </tr>
            <?php while ($p = mysqli_fetch_assoc($products)): ?>
            <tr>
                <td><?= $p['pid'] ?></td>
                <td><?= $p['pname'] ?></td>
                <td><?= $p['ptype'] ?></td>
                <td><?= $p['pplace'] ?></td>
                <td><?= $p['price'] ?>元</td>
                <td><?= $p['stock'] ?></td>
                <td>
                    <?php if (!isAdmin()): ?>
                        <a href="user/buycar.php?act=add&pid=<?= $p['pid'] ?>">加入购物车</a>
                        <a href="user/order_create.php?pid=<?= $p['pid'] ?>&num=1&from=index">立即购买</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>