<?php
/**
 * 主页面入口
 *
 * @author MewX <imewx@qq.com>
 */

/**
 * 全站规划
 **
 * index.php 根据session判断显示登陆界面/主菜单界面（根据S/T显示不同菜单）
 *     学生菜单：消息中心（收件箱/发送消息）
 *              ~修改密码
 *              ~查看毕业设计选题列表（选题、查看详情、哪些人选了）
 *              ~下载专区（模板、老师发的文件）
 *              上传开题报告、毕业论文
 *              ~注销
 *     教师菜单：消息中心（收件箱/发送消息）
 *              ~修改密码
 *              ~查看毕业设计选题列表（查看详情、哪些人选了）
 *              ~下载专区（学生的提交文件列表）
 *              ~发布毕业设计题目
 *              上传评价表、答辩提问录
 *              ~注销
 *
 */

// 如果已经登录则跳过登陆界面部分，如果未登录则显示登陆界面
session_start();
if( !isset($_SESSION['user_id']) ) {
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
    <h1>本科毕业设计<br/>管理系统</h1>
  </div>

  <div data-role="content">
  	<form action="utils/login.php" method="post">
      <label for="text-acc">学号 / 教师号：</label>
      <input type="text" data-clear-btn="false" name="text-acc" id="text-acc" value="">
      <label for="text-pwd">密码：</label>
      <input type="password" data-clear-btn="true" name="text-pwd" id="text-pwd" value="" autocomplete="off">
      <label for="text-cc">验证码：<br/><img src="utils/codeweb.php" height="40" id="verifyCodeImg" style="cursor:pointer;" title="点击更换验证码" onClick="javascript:this.src='utils/codeweb.php'"></label>
      <input type="text" data-clear-btn="false" name="text-cc" id="text-cc" value="">
      <input type="submit" value="登录">
    </form>
  </div>

  <div data-role="footer">
    <div data-role="content">
      <div class="ui-corner-all custom-corners" style="text-align:center">
        <div class="ui-bar ui-bar-a">
          <h3>大学生创新创业训练计划项目</h3>
        </div>
        <div class="ui-body ui-body-a" style="text-align:center">
          <p>xxxx大学<br/>项目编号：xxxxxxxx</p>
        </div>
      </div>
      <div class="ui-corner-all custom-corners" style="text-align:center">
        <div class="ui-bar ui-bar-a">
          <h3>负责人及组员</h3>
        </div>
        <div class="ui-body ui-body-a" style="text-align:center">
          <p>MewX</p>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
<?php
} else {
/* 显示后台页面，需要根据账号类型判断是显示学生菜单还是老师菜单 */
require_once('utils/shell.php');

/* 这里获取一些值给后面调用 */
// 获取收到的Notification数量
$pdo = connectDatabase();
$rs = $pdo->query("select count(notid) from notification where receiver = '" . $_SESSION['user_id']. "' and hasread='N';");
$row = $rs->fetch();
$noti_count = $row['count(notid)'];

// 获取账号所属类别
$rs = $pdo->query("select name, type from account where id = '" . $_SESSION['user_id']. "';");
$row = $rs->fetch();
$acc_name = $row['name'];
$acc_type = $row['type'];
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
    <h1>本科毕业设计<br/>管理系统</h1>
  </div>

  <div data-role="content">
    <? echo $acc_name ?>，欢迎你！
    <a href="notify.php" class="ui-btn ui-corner-all" <? if($noti_count != 0) echo 'style="color:#00F"' ?>>消息中心(<? echo $noti_count ?>)</a>
    <a href="chgpwd.php" class="ui-btn">密码修改</a>
    <a href="problist.php" class="ui-btn">查看毕业设计选题列表</a>
    <a href="download.php" class="ui-btn">下载专区</a>
<?php
if($acc_type == 'S') { // 学生
?>
    <a href="upload.php" class="ui-btn">上传开题报告、毕业论文</a>
<?php
} else { // 教师
?>
    <a href="probpost.php" class="ui-btn">发布毕业设计题目</a>
    <a href="upload.php" class="ui-btn">上传评价表、答辩提问录</a>
<?php
}
?>
    <a href="logout.php" class="ui-btn">注销</a>
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
