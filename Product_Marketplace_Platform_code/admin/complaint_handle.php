<?php
require_once '../common.php';
checkLogin();
if (!isAdmin()) showMsg('管理员功能！', '../index.php');

// 处理投诉
if ($_POST) {
    $cid = $_POST['cid'];
    $cfinish = $_POST['cfinish'];
    $id= $_SESSION['uid'];
    mysqli_query($conn, "UPDATE complaint SET cstate='已处理',cuid=$id, cfinish='$cfinish' WHERE cid=$cid");
    showMsg('投诉处理完成！', 'complaint_handle.php');
}

// 查询未处理投诉
$sql = "SELECT c.*,ct.ctypename, p.pname FROM complaint c JOIN ctype ct ON c.ctypeid=ct.ctypeid JOIN product p ON c.pid=p.pid WHERE c.cstate!='已处理' ORDER BY c.cid ASC"; 
$complaints = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>处理投诉</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>处理投诉</h1>
        <?php while ($c = mysqli_fetch_assoc($complaints)): ?>
        <div style="border:1px solid #ddd; padding:10px; margin:10px 0;">
            <h3>投诉ID：<?= $c['cid'] ?> | 产品：<?= $c['pname'] ?></h3>
            <p>投诉类别：<?= $c['ctypename'] ?></p>
            <p>投诉原因：<?= $c['ctext'] ?></p>
            <form method="post" style="margin-top:10px;">
                <input type="hidden" name="cid" value="<?= $c['cid'] ?>">
                <div class="form-group">
                    <label>处理意见</label>
                    <textarea name="cfinish" rows="2" required></textarea>
                </div>
                <button type="submit">提交处理结果</button>
            </form>
        </div>
        <?php endwhile; ?>
        <a href="../index.php">返回首页</a>
    </div>
</body>
</html>