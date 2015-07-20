<?php
/**
 * 下载文件
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

$result = "";
if($acc_type == 'S') {
	// 学生

	$result = <<< startblock
      <div class="ui-corner-all custom-corners" style="text-align:center">
        <div class="ui-bar ui-bar-a">
startblock;

	$rs = $pdo->query("select tskid, file00, file01, file02, file03, file04 from choice where accid = '" . $_SESSION['user_id']. "';");
	$row = $rs->fetch();

	// 获取task名字
	$rs = $pdo->query("select name from task where id =" . $row['tskid']);
	$rowtemp = $rs->fetch();
	$result = $result . "<h3>" . $rowtemp['name'] . "</h3>" . <<< middleblock
        </div>
        <div class="ui-body ui-body-a" style="text-align:center">
middleblock;

	// 循环获取 fileid
	for($i = 0; $i < 5; $i ++) {
		$queryname = 'file0' . $i;
		if($row[$queryname] == "") continue;
		$rs2 = $pdo->query("select url, name, accid from file where id = " . $row[$queryname]);
		$row2 = $rs2->fetch(); // file info

		// 获取上传人的姓名
		$rs3 = $pdo->query("select name from account where id = '" . $row2['accid'] . "';");
		$row3 = $rs3->fetch(); // $row3['name'] - student info

		// 文件详情输出
		$result = $result . '<a href="' . $row2['url'] . '" class="ui-btn">' . $row3['name'] . '：' . $row2['name'] . '</a>' ;
	}
	$result = $result . <<< endblock
        </div>
      </div>
endblock;
}
else {
	// 老师（拥有多个题目，所以先要获取tskid）
	$rstask = $pdo->query("select id, name from task where holder = '" . $_SESSION['user_id']. "';");
	while($rowtask = $rstask->fetch()) {
		$result = $result . <<< startblock
      <div class="ui-corner-all custom-corners" style="text-align:center">
        <div class="ui-bar ui-bar-a">
startblock;

		// 获取choice 详情
		$result = $result . "<h3>" . $rowtask['name'] . "</h3>" . <<< middleblock
        </div>
        <div class="ui-body ui-body-a" style="text-align:center">
middleblock;
		$rs = $pdo->query("select accid, file00, file01, file02, file03, file04 from choice where tskid = '" . $rowtask['id'] . "';");
		while($row = $rs->fetch()) {

			// 循环获取 fileid
			for($i = 0; $i < 5; $i ++) {
				$queryname = 'file0' . $i;
				if($row[$queryname] == "") continue;
				$rs2 = $pdo->query("select url, name, accid from file where id = " . $row[$queryname]);
				$row2 = $rs2->fetch(); // file info

				// 获取上传人的姓名
				$rs3 = $pdo->query("select name from account where id = '" . $row2['accid'] . "';");
				$row3 = $rs3->fetch(); // $row3['name'] - student info

				// 获取target的姓名
				$rs4 = $pdo->query("select name from account where id = '" . $row['accid'] . "';");
				$row4 = $rs4->fetch(); // $row3['name'] - student info

				// 文件详情输出
				$result = $result . '<a href="' . $row2['url'] . '" class="ui-btn">（' . $row3['name'] . '->' . $row4['name'] . '）' . $row2['name'] . '</a>' ;
			}
		}
		$result = $result . <<< endblock
        </div>
      </div><br/>
endblock;

	}
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>基于Web的本科毕业设计管理系统的设计与实现</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://libs.useso.com/js/jquery-mobile/1.4.2/jquery.mobile.min.css" />
  <script src="http://libs.useso.com/js/jquery/2.1.1/jquery.min.js"></script>
  <script src="http://libs.useso.com/js/jquery-mobile/1.4.2/jquery.mobile.min.js"></script>
  <script src="js/disableAJAX.js"></script>
</head>
<body>
<div data-role="page">
  <div data-role="header">
    <h1>下载中心</h1>
  </div>

  <div data-role="content">
    <? echo $result ?>
    <a href="index.php" class="ui-btn">返回主菜单</a>
  </div>

  <div data-role="footer">
    <h3>xxxx大学·大学生创新创业训练计划项目</h3>
  </div>
</div>
</body>
</html>
