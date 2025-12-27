<?php
require_once '../common.php';
checkLogin();
if (isAdmin()) showMsg('普通用户功能！', '../index.php');

// 安全过滤用户ID
$uid = intval($_SESSION['uid']);

// 1. 查询用户订单对应的产品（加库前缀+参数过滤）
$sql_products = "SELECT DISTINCT p.pid, p.pname 
                 FROM try4_2023150020.orderinfo o 
                 JOIN try4_2023150020.product p ON o.pid=p.pid 
                 WHERE o.uid=$uid";
$products = mysqli_query($conn, $sql_products);
if (!$products) {
    die("订单产品查询失败：" . mysqli_error($conn));
}

// 2. 查询投诉类别（ctype表，加库前缀）
$sql_ctypes = "SELECT ctypeid, ctypename 
               FROM try4_2023150020.ctype";
$ctypes = mysqli_query($conn, $sql_ctypes);
if (!$ctypes) {
    die("投诉类别查询失败：" . mysqli_error($conn));
}

// 3. 处理投诉提交
if ($_POST) {
    // 安全过滤所有参数
    $pid = intval($_POST['pid']);          // 产品ID（整型）
    $ctypeid = intval($_POST['ctypeid']);  // 投诉类别ID（整型，替代原ctype）
    $ctext = mysqli_real_escape_string($conn, $_POST['ctext']); // 投诉原因（转义特殊字符）                        // 投诉人ID
    $cstate = '未处理';

    // 前置校验：必填参数非空
    if (empty($pid)) showMsg('请选择投诉产品！', 'complaint.php');
    if (empty($ctypeid)) showMsg('请选择投诉类别！', 'complaint.php');
    if (empty($ctext)) showMsg('请填写投诉原因！', 'complaint.php');

    // 插入投诉（核心：适配complaint表的ctypeid字段，加库前缀）
    $sql_insert = "INSERT INTO try4_2023150020.complaint 
                   (uid, pid, ctypeid, ctext, cstate) 
                   VALUES ($uid, $pid, $ctypeid, '$ctext', '$cstate')";
    
    if (mysqli_query($conn, $sql_insert)) {
        showMsg('投诉提交成功！', '../index.php');
    } else {
        showMsg('投诉失败：' . mysqli_error($conn), 'complaint.php');
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>提交投诉</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>提交投诉</h1>
        
        <!-- 无订单时提示 -->
        <?php if (mysqli_num_rows($products) == 0): ?>
            <div style="color: #dc3545; padding: 10px; border:1px solid #f5c6cb; border-radius:4px; margin-bottom:20px;">
                暂无有效订单，无法提交投诉！
            </div>
            <a href="../index.php">返回首页</a>
        <?php else: ?>
            <form method="post">
                <!-- 1. 投诉产品选择 -->
                <div class="form-group">
                    <label>投诉产品 <span style="color:red;">*</span></label>
                    <select name="pid" required>
                        <option value="">-- 请选择投诉产品 --</option>
                        <?php while ($p = mysqli_fetch_assoc($products)): ?>
                            <option value="<?= $p['pid'] ?>"><?= $p['pname'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- 2. 投诉类别选择（修正name为ctypeid，值为ctypeid） -->
                <div class="form-group">
                    <label>投诉类别 <span style="color:red;">*</span></label>
                    <select name="ctypeid" required>
                        <option value="">-- 请选择投诉类别 --</option>
                        <?php 
                        // 处理投诉类别为空的情况
                        if (mysqli_num_rows($ctypes) == 0): ?>
                            <option value="" disabled>暂无投诉类别</option>
                        <?php else: 
                            while ($c = mysqli_fetch_assoc($ctypes)): ?>
                                <option value="<?= $c['ctypeid'] ?>"><?= $c['ctypename'] ?></option>
                            <?php endwhile; 
                        endif; ?>
                    </select>
                </div>

                <!-- 3. 投诉原因 -->
                <div class="form-group">
                    <label>投诉原因 <span style="color:red;">*</span></label>
                    <textarea name="ctext" rows="5" placeholder="请详细描述您的投诉原因..." required></textarea>
                </div>

                <button type="submit">提交投诉</button>
                <a href="../index.php" style="margin-left:10px;">返回首页</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>