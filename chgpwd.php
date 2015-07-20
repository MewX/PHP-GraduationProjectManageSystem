<?php
/**
 * 修改密码
 *
 * @author MewX <imewx@qq.com>
 */

require_once('utils/shell.php');

// 如果已经登录则跳过登陆界面部分，如果未登录则显示登陆界面
session_start();
if( !isset($_SESSION['user_id']) ) {
	// goto management page
	header("Location: index.php"); // ==> ../index.php
	exit;
}

// 判断是修改的POST提交，还是显示页面部分
$chgstatus = false;
$msg = "";
if(isset($_POST['text-acc'])) {
	// 是提交来的代码
	if($_POST['text-pwdold'] != "" && $_POST['text-pwdnew1'] != "" && $_POST['text-pwdnew2'] != "") {
		// 判断两次新密码是否相同
		if($_POST['text-pwdnew1'] == $_POST['text-pwdnew2']) {
			// 相同，判断旧的密码是否匹配
			$pwdold = $_POST['text-pwdold'];
			$pwdold = openssl_digest($pwdold,"sha256");
			$pwdold = base64_encode($pwdold);

			$pdo = connectDatabase();
			$rs = $pdo->query("select id from account where id = '" . $_POST['text-acc'].
				"' and (pwd = '" . $pwdold . "' or pwd = NULL );");
			$row = $rs->fetch();
			if($row) {
				// 查到了，正确的密码匹配
				$pwdnew = $_POST['text-pwdnew1'];
				$pwdnew = openssl_digest($pwdnew,"sha256");
				$pwdnew = base64_encode($pwdnew);

				$pdo->exec("update account set pwd = '" . $pwdnew . "' where id = '" . $_POST['text-acc'] . "';");

				$msg = "修改成功！";
				$chgstatus = true;
				$_POST['text-pwdold'] = "";
				$_POST['text-pwdnew1'] = "";
				$_POST['text-pwdnew2'] = "";
			}
			else {
				// 没有索引到记录
				$msg = "原密码错误！";
				$_POST['text-pwdold'] = "";
			}
		}
		else {
			// 不同，重输入
			$msg = "两次输入的新密码不一致。";
			$_POST['text-pwdnew1'] = $_POST['text-pwdnew2'] = "";
		}
	}
	else {
		// 有未填写完成的
		$msg = "表单填写不完整，请检查。";
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
    <h1>密码修改</h1>
  </div>

  <div data-role="content">
    <? if($msg != "") echo '<p style="color:#F00">' . $msg . '</p>' ?>
<?php
if($chgstatus) {
// 密码修改成功
?>
    <a href="index.php" class="ui-btn">返回</a>
<?php
} else {
?>
    <form action="chgpwd.php" method="post">
      <label for="text-acc">用户名：</label>
      <input type="text" readonly name="text-acc" id="text-acc" value="<? echo $_SESSION['user_id'] ?>">
      <label for="text-pwdold">旧密码：</label>
      <input type="password" data-clear-btn="true" name="text-pwdold" id="text-pwdold" value="<? if(isset($_POST['text-pwdold'])) echo $_POST['text-pwdold'] ?>" autocomplete="off">
      <label for="text-pwdnew1">新密码：</label>
      <input type="password" data-clear-btn="true" name="text-pwdnew1" id="text-pwdnew1" value="<? if(isset($_POST['text-pwdnew1'])) echo $_POST['text-pwdnew1'] ?>" autocomplete="off">
      <label for="text-pwdnew2">确认新密码：</label>
      <input type="password" data-clear-btn="true" name="text-pwdnew2" id="text-pwdnew2" value="<? if(isset($_POST['text-pwdnew2'])) echo $_POST['text-pwdnew2'] ?>" autocomplete="off">
      <input type="submit" value="提交修改">
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
