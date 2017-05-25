<?php
/**
 * 通知中心
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

// 初始化pdo
$pdo = connectDatabase();

// 判断是否显示表单页面 / 提交表单
if($_GET['do'] == "post") {
	if($_POST['receiver'] == "" && $_POST['title'] == "" && $_POST['content'] == "") {
		// 填写表单以发送，页面顶部显示相关的通讯录用户id
		echo <<< headerblock
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
    <h1>发送消息</h1>
  </div>

  <div data-role="content">
headerblock;

		// 获取通讯录（目前的替代方案是输出所有的id）
		$rs = $pdo->query("select id, name from account;");
        echo '<h3>通信录</h3>';
		while($row = $rs->fetch()) {
				echo $row['id'] . ' - ' . $row['name'] . '<br/>';
		}

        echo <<< endblock
      <h3>信息发送表单</h3>
      <form action="notify.php?do=post" method="post">
      <label for="receiver">接收者id：</label>
      <input type="text" data-clear-btn="false" name="receiver" id="receiver" value="">
      <label for="title">标题：</label>
      <input type="text" data-clear-btn="false" name="title" id="title" value="">
      <label for="content">内容：</label>
      <textarea name="content" id="content data-enhanced="true" class="ui-input-text ui-shadow-inset ui-body-inherit ui-corner-all"></textarea>
      <input type="submit" value="确认发送">
    </form>
    <a href="index.php" class="ui-btn">返回</a>
  </div>

  <div data-role="footer">
    <h3>xxxx大学·大学生创新创业训练计划项目</h3>
  </div>
</div>
</body>
</html>
endblock;

	}
	else {
		// 检查receiver是否合法，receiver是id，允许发给自己
        $msg = "";
        $switcher = true;

        if($_POST['receiver'] == "" || $_POST['title'] == "" || $_POST['content'] == "") {
        	$msg = "表单填写不完整";
            $switcher = false;
        }

        // 防止SQL注入
        $_POST['receiver'] = _remove_sql_inject($_POST['receiver']);
        $_POST['title'] = _remove_sql_inject($_POST['title']);
        $_POST['content'] = _remove_sql_inject($_POST['content']);

        // 检查receiver 是否存在
        if($switcher) {
            $rs = $pdo->query("select * from account where id = '" . $_POST['receiver'] . "';");
            $row = $rs->fetch();
            if(!$row) {
                $msg = "消息接受者id不存在！";
                $switcher = false;
            }
        }

        // 发送消息，只有当开关变量允许才发送
        if($switcher) {
        	// 获取notid
            $rs = $pdo->query("select count(notid) from notification;");
            $row = $rs->fetch();
            $max_notid = $row['count(notid)'] + 1;

        	$pdo->exec("insert into notification(notid, title, message, sender, receiver) values ('{$max_notid}', '{$_POST['title']}', '{$_POST['content']}', {$_SESSION['user_id']}, {$_POST['receiver']});");
            echo <<< succ
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
    <h1>发送消息</h1>
  </div>

  <div data-role="content">
    <p style="color:#F00">发送消息成功！</p>
    <a href="index.php" class="ui-btn">返回</a>
  </div>

  <div data-role="footer">
    <h3>xxxx大学·大学生创新创业训练计划项目</h3>
  </div>
</div>
</body>
</html>
succ;
        }
    }
    exit;
}

// 设置为已读（执行失败！）
$pdo->exec("update notification set hasread = 'Y' where receiver = '" . $_SESSION['user_id'] . "';");

// 获取notify列表
$rs = $pdo->query("select * from notification where receiver = '" . $_SESSION['user_id'] . "' order by notid desc;");
$result = "";
while($row = $rs->fetch()) {
	$rs2 = $pdo->query("select name from account where id = '" . $row['sender'] . "';");
    $row2 = $rs2->fetch();

	$result = $result . <<< block
<div class="ui-corner-all custom-corners" style="text-align:center">
  <div class="ui-bar ui-bar-a">
    <h3>{$row2['name']}：{$row['title']}</h3>
  </div>
  <div class="ui-body ui-body-a" style="text-align:center">
    <p>{$row['message']}</p>
  </div>
</div><br/>
block;
}


echo <<< htmlblock
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
    <h1>消息中心</h1>
  </div>

  <div data-role="content">
    <a href="notify.php?do=post" class="ui-btn">发送消息</a><br/>
    {$result}
    <a href="index.php" class="ui-btn">返回</a>
  </div>

  <div data-role="footer">
    <h3>xxxx大学·大学生创新创业训练计划项目</h3>
  </div>
</div>
</body>
</html>
htmlblock;
?>
