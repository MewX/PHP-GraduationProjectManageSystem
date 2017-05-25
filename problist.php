<?php
/**
 * 选题列表
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

// 判断是学生还是老师
$pdo = connectDatabase();
$rs = $pdo->query("select type from account where id = '" . $_SESSION['user_id']. "';");
$row = $rs->fetch();
$acc_type = $row['type']; // 'S' / 'T'

if($_GET['taskid'] != '') {
	if($_GET['do'] == 'yes') {
		// 选择题目
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
    <h1>选题结果</h1>
  </div>

  <div data-role="content">
<?php
		// 这里应该使用级联查询和同步锁！！！但是我忘了！！！暂时的替代方案。
		// 首先判断是否容量足够
		$rs = $pdo->query("select capacity from task where id = " . $_GET['taskid'] . ";");
		$row = $rs->fetch();
		if(!$row) exit;
		$max_capacity = $row['capacity'];

		// 获取已选人数
		$rs = $pdo->query("select count(accid) from choice where tskid = " . $_GET['taskid'] . ";");
		$row = $rs->fetch();
		$current_capacity = $row['count(accid)'];

		if($max_capacity > $current_capacity) {
			$pdo->exec('insert into choice(accid, tskid) values (' . $_SESSION['user_id'] . ', ' . $_GET['taskid'] . ')');
			echo '成功选定该题目！不可再更改！';
		}
		else {
			echo "容量已满，请选择其他题目！";
		}
?>
    <a href="problist.php" class="ui-btn">返回</a>
  </div>

  <div data-role="footer">
    <h3>xxxx大学·大学生创新创业训练计划项目</h3>
  </div>
</div>
</body>
</html>
<?php
	}
	else {
		// 只是查看信息
		$rs = $pdo->query("select * from task where id = '" . $_GET['taskid'] . "';");
		$row = $rs->fetch();
		$tskid = $row['id'];
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
    <h1>选题详情</h1>
  </div>

  <div data-role="content">
    <div class="ui-corner-all custom-corners" style="text-align:center">
      <div class="ui-bar ui-bar-a">
        <h3><? echo $row['id'] . ' - ' . $row['name']; ?></h3>
      </div>
      <div class="ui-body ui-body-a" style="text-align:left">
        <p><? echo $row['content']; ?></p>
      </div>
    </div>
<?php
		// 判断是否已选过题目了
		$rs = $pdo->query("select tskid from choice where accid = '" . $_SESSION['user_id']. "';");
		$row = $rs->fetch();
		if(!$row && $acc_type == 'S')
			echo '<a href="problist.php?taskid=' . $tskid . '&do=yes" class="ui-btn">选定题目（一旦选定则不可更改）</a>';
		else
			echo '<a href="#" class="ui-btn ui-state-disabled">选定题目（一旦选定则不可更改）</a>';

		// 选择这门课的其他人列表
		$acclist = "选择本题的同学：";
		$rs = $pdo->query("select accid from choice where tskid = '" . $tskid. "';");
		while($row = $rs->fetch()) {
			$rs2 = $pdo->query("select name from account where id = '" . $row['accid'] . "';");
			$row2 = $rs2->fetch();
			$acclist = $acclist . $row2['name'] . '  ';
		}
		echo $acclist;
?>
    <a href="problist.php" class="ui-btn">返回题目列表</a>
  </div>

  <div data-role="footer">
    <h3>xxxx大学·大学生创新创业训练计划项目</h3>
  </div>
</div>
</body>
</html>
<?php
	}
}
else {
	// 清单列表
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
    <h1>选题列表</h1>
  </div>

  <div data-role="content">
<?php
	// 判断是学生还是老师
	//if($acc_type == 'S') {
		// 学生
		$rs = $pdo->query("select accid, tskid from choice where accid = '" . $_SESSION['user_id']. "';");
		$row = $rs->fetch();
		if($row) {
			// 选择过了，把选过的先展现出来
			$rs = $pdo->query("select id, name from task where id = '" . $row['tskid']. "';");
			$row = $rs->fetch();
?>
    <fieldset data-role="controlgroup">
      <legend>已选择的题目：</legend>
      <? echo '<a href="problist.php?taskid=' . $row['id'] . '" class="ui-btn ui-corner-all">' . $row['id'] . ' - ' . $row['name'] . '</a>'; ?>
    </fieldset>
<?php
		}
?>
    <div data-role="controlgroup" class="ui-controlgroup ui-controlgroup-vertical ui-corner-all">
      <div class="ui-controlgroup-controls ">
        <legend>所有题目列表（点击查看详情）：</legend>
<?php
		// 未选择，所以展示选择界面
		$rs = $pdo->query("select id, name, capacity, holder from task;");
		while($row = $rs->fetch()) {
			// 获取教师姓名
			$rs2 = $pdo->query("select name from account where id = '" . $row['holder'] . "';");
			$row2 = $rs2->fetch();

			// 获取已选人数
			$rs2 = $pdo->query("select count(accid) from choice where tskid = '" . $row['id'] . "';");
			$row3 = $rs2->fetch();

			//echo '<input type="radio" name="radio-' . $row['id'] . '" id="radio-' . $row['id'] . '" value="' . $row['id'] . '">';
			//if($row3['count(accid)'] < $row['capacity'])
			//	echo '<label for="radio-' . $row['id'] . '">' . $row2['name'] . ' - ' . $row['name'] . '（' . $row3['count(accid)'] . ' / ' . $row['capacity'] . '）</label>';
			//else
			//
			//	echo '<label for="radio-' . $row['id'] . '" disabled="disabled">' . $row2['name'] . ' - ' . $row['name'] . '（' . $row3['count(accid)'] . ' / ' . $row['capacity'] . '）</label>';
			echo '<a href="problist.php?taskid=' . $row['id'] . '" class="ui-btn ui-corner-all">' . $row2['name'] . ' - ' . $row['name'] . '（' . $row3['count(accid)'] . ' / ' . $row['capacity'] . '）</a>';
		}
?>
      </div>
    </div>
<?php

?>

    <a href="index.php" class="ui-btn">返回主菜单</a>
  </div>

  <div data-role="footer">
    <h3>xxxx大学·大学生创新创业训练计划项目</h3>
  </div>
</div>
</body>
</html>
<?php
}
?>
