<?php
/**
 * 上传文件（此部分链接SAE未完成）
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

// 这里添加SaeStorage的存储方法 sample
//$s2 = new SaeStorage();
//$name =$_FILES['myfile']['name'];
//echo $s2->upload('test',$name,$_FILES['myfile']['tmp_name']);//把用户传到SAE的文件转存到名为test的storage
//// echo $s2->getUrl("test",$name);//输出文件在storage的访问路径
//echo '<br/>';
//echo $s2->errmsg(); //输出storage的返回信息

// 判断是学生还是老师
$pdo = connectDatabase();
$rs = $pdo->query("select type from account where id = '" . $_SESSION['user_id']. "';");
$row = $rs->fetch();
$acc_type = $row['type']; // 'S' / 'T'

// 获取分块数据
function getBlock($title, $content) {
	return <<< block
        <div class="ui-bar ui-bar-a">
          <h3>{$title}</h3>
        </div>
        <div class="ui-body ui-body-a" style="text-align:center">
          <p>{$content}</p>
        </div>
block;
}
function getFileInput($inputid, $info, $fileexist) {
	return <<< block
    <label for="{$inputid}">上传{$info}：{$fileexist}</label>
    <input type="file" data-clear-btn="false" name="{$inputid}" id="{$inputid}" value="">
block;
}

$result = "";
if($acc_type == 'S') {
	// 学生，只有一个题目：开题报告、毕业论文
	$rs = $pdo->query("select tskid, file01, file02 from choice where accid = '" . $_SESSION['user_id']. "';");
	$row = $rs->fetch();
	$file01 = $row['file01'] == "" ? '未上传' : '已上传，继续上传将覆盖';
	$file02 = $row['file02'] == "" ? '未上传' : '已上传，继续上传将覆盖';

	// 获取选题信息
	$rs = $pdo->query("select name from task where id = '" . $row['tskid'] . "';");
	$row = $rs->fetch();
	$result = getBlock($row['name'], getFileInput($_SESSION['user_id'] . '-1', '开题报告', $file01) .  getFileInput($_SESSION['user_id'] . '-2', '毕业论文', $file01)); // use split

}
else {
	// 教师，多个题目：上传每一个的题目的：任务书及模板、评价表、答辩提问录
	$rstask = $pdo->query("select id, name from task where holder = '" . $_SESSION['user_id']. "';");
	while($rowtask = $rstask->fetch()) {
		$rs = $pdo->query("select tskid, file01, file02 from choice where tskid = '" . $row['id'] . "';");
		$row = $rs->fetch();
		$file01 = $row['file01'] == "" ? '未上传' : '已上传，继续上传将覆盖';
		$file02 = $row['file02'] == "" ? '未上传' : '已上传，继续上传将覆盖';

		// 获取选题信息
		$rs = $pdo->query("select name from task where id = '" . $row['tskid'] . "';");
		$row = $rs->fetch();
		$result = $result . getBlock($rowtask['name'], getFileInput($_SESSION['user_id'] . '-1', '开题报告', $file01) .  getFileInput($_SESSION['user_id'] . '-2', '毕业论文', $file01)); // use split
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
    <h1>上传中心</h1>
  </div>

  <div data-role="content">
    <? echo $result ?>
    <a href="index.php" class="ui-btn">返回</a>
  </div>

  <div data-role="footer">
    <h3>xxxx大学·大学生创新创业训练计划项目</h3>
  </div>
</div>
</body>
</html>
