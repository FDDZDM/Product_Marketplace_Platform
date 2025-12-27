<?php
require_once '../common.php';
checkLogin();
if (isAdmin()) showMsg('普通用户功能！', '../index.php');

// 获取产品类型
$types = mysqli_query($conn, "SELECT * FROM producttype");

if ($_POST) {
    $pname = $_POST['pname'];
    $ptypeid = $_POST['ptypeid'];
    $pplace = $_POST['pplace'];
    $pdescription = $_POST['pdescription'];
    $price = $_POST['price'];
    $plife = $_POST['plife'];
    $stock = $_POST['stock'];
    $pdate = date('Y-m-d'); // 当前日期

    // 插入产品
    $sql = "INSERT INTO product (pname, ptypeid, seller_uid, pplace, pdescription, 
            pdate, plife, pstate, dropreason, price, stock) 
            VALUES ('$pname', $ptypeid, {$_SESSION['uid']}, '$pplace', '$pdescription',
            '$pdate', $plife, '上架', '', $price, $stock)";
    if (mysqli_query($conn, $sql)) {
        showMsg('产品发布成功！', '../index.php');
    } else {
        showMsg('发布失败：' . mysqli_error($conn), 'product_publish.php');
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>发布产品</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>发布时令产品</h1>
        <form method="post">
            <div class="form-group">
                <label>产品名</label>
                <input type="text" name="pname" required>
            </div>
            <div class="form-group">
                <label>产品类别</label>
                <select name="ptypeid" required>
                    <?php while ($t = mysqli_fetch_assoc($types)): ?>
                        <option value="<?= $t['ptypeid'] ?>"><?= $t['ptype'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>产地</label>
                <input type="text" name="pplace" required>
            </div>
            <div class="form-group">
                <label>产品描述</label>
                <textarea name="pdescription" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label>销售价格（元）</label>
                <input type="number" step="0.01" name="price" required>
            </div>
            <div class="form-group">
                <label>销售期（天）</label>
                <input type="number" name="plife" required>
            </div>
            <div class="form-group">
                <label>库存数量</label>
                <input type="number" name="stock" required>
            </div>
            <button type="submit">发布</button>
            <a href="../index.php">返回首页</a>
        </form>
    </div>
</body>
</html>