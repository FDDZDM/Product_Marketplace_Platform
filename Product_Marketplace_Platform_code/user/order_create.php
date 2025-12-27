<?php
require_once '../common.php';
checkLogin();
if (isAdmin()) showMsg('普通用户功能！', '../index.php');

// 调试：查看参数传递
echo "【调试】URL from：" . ($_GET['from'] ?? '无') . " | POST from：" . ($_POST['from'] ?? '无') . "<br>";

// 1. 接收来源参数 + 基础参数
$pid = intval($_GET['pid'] ?? 0);
$default_num = intval($_GET['num'] ?? 1);
$from_page = $_GET['from'] ?? 'cart'; // 优先取URL的from
$default_num = $default_num < 1 ? 1 : $default_num;

// 2. 校验商品ID
if ($pid == 0) {
    $back_url = $from_page == 'index' ? '../index.php' : './buycar.php';
    showMsg('非法访问！', $back_url);
    exit;
}

// 3. 查询商品信息
$db_prefix = 'try4_2023150020.';
$sql_product = "SELECT * FROM {$db_prefix}product WHERE pid=$pid AND pstate='上架'";
$res_product = mysqli_query($conn, $sql_product);
if (!$res_product || mysqli_num_rows($res_product) == 0) {
    $back_url = $from_page == 'index' ? '../index.php' : './buycar.php';
    showMsg('产品不存在或已下架！', $back_url);
    exit;
}
$product = mysqli_fetch_assoc($res_product);
$stock = intval($product['stock']);
$unit_price = floatval($product['price']);

// 4. 处理下单提交
if ($_POST) {
    $final_num = intval($_POST['num'] ?? 1);
    $from_page = $_POST['from'] ?? 'index'; // 取表单隐藏域的from
    $uid = intval($_SESSION['uid']);

    // 库存校验
    if ($final_num < 1 || $final_num > $stock) {
        $back_url = $from_page == 'index' ? "../order_create.php?pid=$pid&num=$final_num&from=index" : "../order_create.php?pid=$pid&num=$final_num&from=cart";
        showMsg($final_num < 1 ? '数量不能小于1！' : "库存不足（仅剩{$stock}件）", $back_url);
        exit;
    }

    // 计算金额 + 执行下单
    $final_pay = round($unit_price * $final_num, 2);
    $otime = date('Y-m-d');
    mysqli_begin_transaction($conn);
    try {
        // 插入订单
        $sql_order = "INSERT INTO {$db_prefix}orderinfo (pid, uid, otime, ostate, opay) VALUES ($pid, $uid, '$otime', '已支付', $final_pay)";
        if (!mysqli_query($conn, $sql_order)) throw new Exception('创建订单失败：' . mysqli_error($conn));
        
        // 扣减库存
        $sql_stock = "UPDATE {$db_prefix}product SET stock=stock-$final_num WHERE pid=$pid";
        if (!mysqli_query($conn, $sql_stock)) throw new Exception('扣减库存失败：' . mysqli_error($conn));
        
        // 删除购物车商品（仅购物车下单时执行）
        if ($from_page == 'cart') {
            $sql_cart = "DELETE FROM {$db_prefix}buycar WHERE pid=$pid AND uid=$uid";
            mysqli_query($conn, $sql_cart);
        }

        mysqli_commit($conn);
        // 强校验跳转逻辑
        $back_url = strtolower(trim($from_page)) == 'index' ? '../index.php' : './buycar.php';
        echo "【调试】下单成功，跳转URL：$back_url <br>"; // 调试用
        showMsg("下单成功！支付金额：{$final_pay}元", $back_url);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $back_url = $from_page == 'index' ? "../order_create.php?pid=$pid&num=$final_num&from=index" : "../order_create.php?pid=$pid&num=$final_num&from=cart";
        showMsg('下单失败：'.$e->getMessage(), $back_url);
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>确认订单</title>
    <style>
        .container {width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #eee;}
        .num-input {width: 80px; padding: 6px; text-align: center; margin: 0 10px;}
        .final-pay {color: red; font-size: 20px; font-weight: bold; margin-top: 15px;}
        button {padding: 10px 30px; background: #007bff; color: #fff; border: none; cursor: pointer;}
    </style>
</head>
<body>
    <div class="container">
        <form method="post">
            <h1>购买：<?= htmlspecialchars($product['pname']) ?></h1>
            <p>单价：<?= $unit_price ?>元 | 库存：<?= $stock ?>件</p>
            <p>购买数量：
                <input type="number" id="buyNum" name="num" class="num-input"
                       min="1" max="<?= $stock ?>" value="<?= $default_num ?>"
                       oninput="calcPay()">
            </p>
            <p class="final-pay">最终支付金额：<span id="finalPay"><?= $unit_price * $default_num ?></span>元</p>
            <!-- 强制传递URL中的from参数 -->
            <input type="hidden" name="from" value="<?= trim($_GET['from'] ?? 'cart') ?>">
            <button type="submit">确认下单</button>
            <!-- 返回按钮也强制用URL的from -->
            <a href="<?= trim($_GET['from'] ?? 'cart') == 'index' ? '../index.php' : './buycar.php' ?>" style="margin-left:15px;">返回</a>
        </form>
    </div>

    <script>
        function calcPay() {
            const num = document.getElementById('buyNum').value;
            document.getElementById('finalPay').innerText = (<?= $unit_price ?> * num).toFixed(2);
        }
    </script>
</body>
</html>