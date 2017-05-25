<?php
/**
 * 发布选题
 *
 * @author MewX <imewx@qq.com>
 */

require_once('utils/shell.php');
require_once('utils/sql-no-injection.php');

// 判断是否已经登录
session_start();
if( !isset($_SESSION['user_id']) ) {
	// goto management page
	header("Location: index.php"); // ==> ../index.php
	exit;
}

// Teacher only
$pdo = connectDatabase();
$rs = $pdo->query("select type from account where id = '" . $_SESSION['user_id']. "';");
$row = $rs->fetch();
if($row['type'] != 'T') {
	// goto management page
	header("Location: index.php"); // ==> ../index.php
	exit;
}

// 处理post内容
$isok = false;
$msg = "";
if($_POST['text-title'] != "" && $_POST['text-capacity'] != "" && $_POST['text-content'] != "") {
	if(is_int($_POST['text-capacity'] / 1) && $_POST['text-capacity'] > 0 && $_POST['text-capacity'] < 100) {
		// 获取id的最大值
		$rs = $pdo->query("select max(id) from task;");
		$row = $rs->fetch();
		$max_id = $row['max(id)'] + 1;

		// 防止SQL注入
		$_POST['text-title'] = _remove_sql_inject($_POST['text-title']);
		$_POST['text-content'] = _remove_sql_inject($_POST['text-content']);

		// 添加到数据库中
		$pdo->exec("insert into task(id, name, content, capacity, holder) values (" . $max_id . ", '" .
			$_POST['text-title'] . "', '" . $_POST['text-content'] . "', " . $_POST['text-capacity'] . ", " . $_SESSION['user_id'] . ");");
		$msg = "题目添加完成，可以到题目列表中查看。";
		$isok = true;
	}
	else {
		$msg = "选题的最大人数必须为合理大小的正整数！" . is_int($_POST['text-capacity']);
	}
}
elseif($_POST['text-title'] == "" && $_POST['text-capacity'] == "" && $_POST['text-content'] == "") {
	 // 合理的情况
}
else {
	$msg = "信息填写不完整！";
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>基于Web的本科毕业设计管理系统的设计与实现</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
  <script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
  <script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
  <script src="js/disableAJAX.js"></script>
</head>
<body>
<div data-role="page">
  <div data-role="header">
    <h1>创建选题</h1>
  </div>

  <div data-role="content">
    <? if($msg != "") echo '<p style="color:#F00">' . $msg . '</p>' ?>
<?php
if($isok) {
?>
    <a href="index.php" class="ui-btn">返回</a>
<?php
} else {
?>
    <form action="probpost.php" method="post">
      <label for="text-title">题目：</label>
      <input type="text" data-clear-btn="false" name="text-title" id="text-title" value="<? if(isset($_POST['text-title'])) echo $_POST['text-title'] ?>">
      <label for="text-capacity">选题最大人数（1-100）：</label>
      <input type="text" data-clear-btn="false" name="text-capacity" id="text-capacity" value="<? if(isset($_POST['text-capacity'])) echo $_POST['text-capacity'] ?>">
      <label for="text-content">内容：</label>
      <textarea name="text-content" id="text-content" data-enhanced="true" class="ui-input-text ui-shadow-inset ui-body-inherit ui-corner-all"><? if(isset($_POST['text-content'])) echo $_POST['text-content'] ?></textarea>
      <input type="submit" value="添加毕业设计题目">
    </form>
<?php
}
?>
  </div>

  <div data-role="footer">
    <h3>xxxx大学·大学生创新创业训练计划项目</h3>
  </div>
</div>
</body>
</html>
