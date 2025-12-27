<?php
require_once '../common.php';
checkLogin(); // 确保用户已登录
if (isAdmin()) showMsg('普通用户功能！', '../index.php'); // 管理员不可访问

// 数据库前缀
$db_prefix = 'try4_2023150020.';
$user_uid = intval($_SESSION['uid']); // 当前登录用户ID

// 1. 处理「自主下架」操作
if (isset($_GET['act']) && $_GET['act'] == 'self_drop' && isset($_GET['pid'])) {
    $pid = intval($_GET['pid']);
    
    // 关键校验：仅允许下架自己上架的商品（seller_uid = 当前用户uid）
    $check_sql = "SELECT pid FROM {$db_prefix}product WHERE pid={$pid} AND seller_uid={$user_uid}";
    $check_res = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($check_res) == 0) {
        showMsg('无权下架该商品（非本人上架）！', 'user_product_manage.php');
        exit;
    }

    // 更新商品状态为「自主下架」，记录下架原因
    $update_sql = "UPDATE {$db_prefix}product 
                   SET pstate='自主下架', dropreason='用户自主下架' 
                   WHERE pid={$pid} AND seller_uid={$user_uid}";
    $update_res = mysqli_query($conn, $update_sql);
    
    if (!$update_res) {
        showMsg('下架失败：' . mysqli_error($conn), 'user_product_manage.php');
    } else {
        showMsg('商品已自主下架！', 'user_product_manage.php');
    }
}
// 在「处理自主下架」逻辑后添加
if (isset($_GET['act']) && $_GET['act'] == 'restore' && isset($_GET['pid'])) {
    $pid = intval($_GET['pid']);
    // 仅恢复自己上架、且状态为「自主下架」的商品
    $check_sql = "SELECT pid FROM {$db_prefix}product WHERE pid={$pid} AND seller_uid={$user_uid} AND pstate='自主下架'";
    $check_res = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($check_res) == 0) {
        showMsg('无权恢复该商品！', 'user_product_manage.php');
        exit;
    }
    $update_sql = "UPDATE {$db_prefix}product SET pstate='上架', dropreason='' WHERE pid={$pid} AND seller_uid={$user_uid}";
    $update_res = mysqli_query($conn, $update_sql);
    if (!$update_res) {
        showMsg('恢复上架失败：' . mysqli_error($conn), 'user_product_manage.php');
    } else {
        showMsg('商品已恢复上架！', 'user_product_manage.php');
    }
}
// 2. 查询当前用户上架的所有商品
$product_sql = "SELECT p.*, pt.ptype 
                FROM {$db_prefix}product p 
                LEFT JOIN {$db_prefix}producttype pt ON p.ptypeid=pt.ptypeid 
                WHERE p.seller_uid={$user_uid} 
                ORDER BY p.pid DESC";
$products = mysqli_query($conn, $product_sql);
if (!$products) {
    die("商品列表查询失败：" . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>我的上架商品管理</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container {width: 90%; max-width: 1200px; margin: 50px auto;}
        .product-table {width: 100%; border-collapse: collapse; margin: 20px 0;}
        .product-table th, .product-table td {
            border: 1px solid #ddd; padding: 12px; text-align: center;
        }
        .product-table th {background: #f8f9fa; font-weight: 500;}
        .status-on {color: #28a745; font-weight: 500;} /* 上架 */
        .status-self-drop {color: #ffc107;} /* 自主下架 */
        .status-illegal-drop {color: #dc3545;} /* 违规下架 */
        .drop-btn {
            display: inline-block;
            padding: 6px 12px;
            background: #ffc107;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
        .drop-btn:hover {background: #ffb300;}
        .back-btn {margin-top: 20px; display: inline-block; color: #007bff; text-decoration: none;}
        .empty-tip {text-align: center; padding: 30px; color: #6c757d;}
    </style>
</head>
<body>
    <div class="container">
        <h1>我的上架商品管理</h1>
        <table class="product-table">
            <tr>
                <th>商品ID</th>
                <th>商品名称</th>
                <th>商品类型</th>
                <th>价格</th>
                <th>库存</th>
                <th>状态</th>
                <th>操作</th>
            </tr>
            <?php if (mysqli_num_rows($products) == 0): ?>
                <tr><td colspan="7" class="empty-tip">暂无上架商品</td></tr>
            <?php else: ?>
                <?php while ($p = mysqli_fetch_assoc($products)): ?>
                <tr>
                    <td><?= $p['pid'] ?></td>
                    <td><?= htmlspecialchars($p['pname']) ?></td>
                    <td><?= htmlspecialchars($p['ptype'] ?? '未知类型') ?></td>
                    <td><?= number_format($p['price'], 2) ?>元</td>
                    <td><?= $p['stock'] ?></td>
                    <td class="<?= 
                        $p['pstate'] == '上架' ? 'status-on' : 
                        ($p['pstate'] == '自主下架' ? 'status-self-drop' : 'status-illegal-drop') 
                    ?>">
                        <?= $p['pstate'] ?>
                    </td>
                    <td>
                        <?php if ($p['pstate'] == '上架'): // 仅上架状态显示下架按钮 ?>
                            <a href="user_product_manage.php?act=self_drop&pid=<?= $p['pid'] ?>" 
                               class="drop-btn" onclick="return confirm('确定下架该商品吗？下架后可联系管理员恢复！')">
                                自主下架
                            </a>
                        <?php else: ?>
                            <a href="user_product_manage.php?act=restore&pid=<?= $p['pid'] ?>" 
                               class="drop-btn" onclick="return confirm('确定恢复上架该商品吗？')">
                                恢复上架
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </table>
        <a href="../index.php" class="back-btn">返回首页</a>
    </div>
</body>
</html>