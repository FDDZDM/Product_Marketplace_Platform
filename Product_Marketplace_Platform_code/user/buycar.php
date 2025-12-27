<?php
require_once '../common.php';
checkLogin();
if (isAdmin()) showMsg('普通用户功能！', '../index.php');

// 1. 处理购物车操作：加入/删除/增减数量
if (isset($_GET['act'])) {
    $uid = intval($_SESSION['uid']);
    $pid = intval($_GET['pid']);
    
    // 通用SQL错误处理函数
    function handleSqlError($conn, $msg) {
        showMsg($msg . '：' . mysqli_error($conn), 'buycar.php');
    }

    switch ($_GET['act']) {
        // 加入购物车（原有功能）
        case 'add':
            $sql_check = "SELECT * FROM try4_2023150020.product WHERE pid=$pid AND pstate='上架'";
            $res_check = mysqli_query($conn, $sql_check);
            if (!$res_check) handleSqlError($conn, '产品查询失败');
            if (mysqli_num_rows($res_check) == 0) {
                showMsg('产品不存在或已下架！', 'buycar.php');
            }

            $sql_cart = "SELECT * FROM try4_2023150020.buycar WHERE uid=$uid AND pid=$pid";
            $res_cart = mysqli_query($conn, $sql_cart);
            if (!$res_cart) handleSqlError($conn, '购物车查询失败');

            if (mysqli_num_rows($res_cart) > 0) {
                $sql_update = "UPDATE try4_2023150020.buycar SET bnum=bnum+1 WHERE uid=$uid AND pid=$pid";
                $res_update = mysqli_query($conn, $sql_update);
                if (!$res_update) handleSqlError($conn, '购物车更新失败');
            } else {
                $sql_insert = "INSERT INTO try4_2023150020.buycar (uid, pid, bnum) VALUES ($uid, $pid, 1)";
                $res_insert = mysqli_query($conn, $sql_insert);
                if (!$res_insert) handleSqlError($conn, '加入购物车失败');
            }
            showMsg('加入购物车成功！', 'buycar.php');
            break;

        // 删除购物车商品
        case 'delete':
            $sql_delete = "DELETE FROM try4_2023150020.buycar WHERE uid=$uid AND pid=$pid";
            $res_delete = mysqli_query($conn, $sql_delete);
            if (!$res_delete) handleSqlError($conn, '删除失败');
            showMsg('商品已从购物车删除！', 'buycar.php');
            break;

        // 增加数量
        case 'addnum':
            $sql_update = "UPDATE try4_2023150020.buycar SET bnum=bnum+1 WHERE uid=$uid AND pid=$pid";
            $res_update = mysqli_query($conn, $sql_update);
            if (!$res_update) handleSqlError($conn, '数量增加失败');
            break;

        // 减少数量（最小为1）
        case 'minusnum':
            // 先查询当前数量
            $sql_check_num = "SELECT bnum FROM try4_2023150020.buycar WHERE uid=$uid AND pid=$pid";
            $res_check_num = mysqli_query($conn, $sql_check_num);
            if (!$res_check_num) handleSqlError($conn, '数量查询失败');
            
            $row = mysqli_fetch_assoc($res_check_num);
            if ($row['bnum'] <= 1) {
                showMsg('数量不能小于1！', 'buycar.php');
            } else {
                $sql_update = "UPDATE try4_2023150020.buycar SET bnum=bnum-1 WHERE uid=$uid AND pid=$pid";
                $res_update = mysqli_query($conn, $sql_update);
                if (!$res_update) handleSqlError($conn, '数量减少失败');
            }
            break;
    }
}

// 2. 修复核心：先将用户ID赋值给变量，再拼接SQL（避免字符串内写函数）
$user_id = intval($_SESSION['uid']); // 独立变量存储，避免语法错误
$sql = "SELECT b.*, p.pname, p.price 
        FROM try4_2023150020.buycar b 
        LEFT JOIN try4_2023150020.product p ON b.pid=p.pid 
        WHERE b.uid=$user_id"; // 直接使用变量，无语法问题
$buycars = mysqli_query($conn, $sql);
if (!$buycars) {
    die("购物车数据查询失败：" . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>我的购物车</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* 数量操作按钮样式 */
        .num-btn {
            display: inline-block;
            width: 25px;
            height: 25px;
            line-height: 25px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
        }
        .num-btn:hover {
            background: #f5f5f5;
            text-decoration: none;
        }
        .num-input {
            width: 50px;
            height: 25px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 0 5px;
        }
        .del-btn {
            color: #dc3545;
            border-color: #dc3545;
        }
        .del-btn:hover {
            background: #dc3545;
            color: #fff;
        }
        .buy-btn {
            color: #28a745;
            border-color: #28a745;
        }
        .buy-btn:hover {
            background: #28a745;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>我的购物车</h1>
        <table class="table">
            <tr>
                <th>产品名</th>
                <th>单价</th>
                <th>数量</th>
                <th>小计</th>
                <th>操作</th>
            </tr>
            <?php 
            if (mysqli_num_rows($buycars) == 0) {
                echo '<tr><td colspan="5" align="center">您的购物车为空</td></tr>';
            } else {
                while ($bc = mysqli_fetch_assoc($buycars)): 
                    $pname = $bc['pname'] ?? '已下架产品';
                    $price = $bc['price'] ?? 0;
                    $subtotal = $price * $bc['bnum'];
                    $pid = $bc['pid'];
                ?>
                <tr>
                    <td><?= $pname ?></td>
                    <td><?= number_format($price, 2) ?>元</td>
                    <td>
                        <!-- 数量减少按钮 -->
                        <a href="buycar.php?act=minusnum&pid=<?= $pid ?>" class="num-btn">-</a>
                        <!-- 当前数量 -->
                        <input type="text" value="<?= $bc['bnum'] ?>" class="num-input" readonly>
                        <!-- 数量增加按钮 -->
                        <a href="buycar.php?act=addnum&pid=<?= $pid ?>" class="num-btn">+</a>
                    </td>
                    <td><?= number_format($subtotal, 2) ?>元</td>
                    <td>
                        <!-- 修复：删除from参数的单引号和空格 -->
                        <a href="order_create.php?pid=<?= $pid ?>&num=<?= $bc['bnum'] ?>&from=cart" class="num-btn buy-btn" style="margin-right:5px;">买</a>
                        <!-- 删除按钮（增加确认提示） -->
                        <a href="buycar.php?act=delete&pid=<?= $pid ?>" class="num-btn del-btn" onclick="return confirm('确定删除该商品吗？')">删</a>
                    </td>
                </tr>
                <?php endwhile; 
            } ?>
        </table>
        <a href="../index.php">返回首页</a>
    </div>
</body>
</html>