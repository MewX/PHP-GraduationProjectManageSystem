<?php
/**
 * 登录校验
 *
 * @author MewX <imewx@qq.com>
 */

require_once('shell.php');
require_once('sql-no-injection.php');

session_start();
//$in_debug_mode = 1;

// test is logged
if( isset($_SESSION['user_id']) ) {
	// goto management page
	header("Location: ../"); // ==> ../index.php
	exit;
}

// avoid direct access
if(!isset($_SESSION['code'])) {
	  echo "[ WARNING ] DO NOT TRY TO HACK THIS TINY SYSTEM!<br/>";
	  echo "[ Technical Support: mewx@mewx.org ]";
	  exit;
}

// only allow from post
// pwd transferred in plaintext.
// ( securer transferred by base64(sha256), but not here )
$pos = 0;
if($_SERVER['REQUEST_METHOD'] == 'POST'){
	if($in_debug_mode) echo "POST detected<br/>";
	
	// test ck
	$post_ck = $_POST['text-cc'];
	$post_ck = strtolower($post_ck);
	if($in_debug_mode) echo $post_ck . ", " . $_POST['text-cc'] . ", " . $_SESSION['code'];
	
	$pos=1;
	// 检查验证码
	if( $post_ck == $_SESSION['code'] ) {
		
		$post_name = $_POST['text-acc'];
		$post_name = _remove_sql_inject($post_name);
		$post_pwd = $_POST['text-pwd'];
		$post_pwd = openssl_digest($post_pwd,"sha256");
		$post_pwd = base64_encode($post_pwd);
		
		$pos=2;
		// 检查用户名和密码是否为空
		if($post_name && $post_pwd) {
			$pdo = connectDatabase();
			$rs = $pdo->query("select id from account where id = '" . $post_name.
				"' and (pwd = '" . $post_pwd . "' or pwd = NULL );");
			$row = $rs->fetch();
			if(!$row) {
				echo "<script>alert('账号不存在或密码错误！');location='../';</script>";
				exit;
			}
			else {
				if(isset($_SESSION['user_id'])) session_unregister('user_id');
				$_SESSION['user_id'] = $post_name;
				
				// goto management page
				header("Location: ../"); // ==> ../index.php
				exit;
			}
		}
		else {
			echo "<script>alert('用户名或密码为空!');location='../';</script>";
			exit;
		}
	}
	else {
		echo "<script>alert('验证码错误!');location='../';</script>";
		exit;
	}
}
else
	header("Location: ../"); // ==> ../index.php

// jump to login.php
echo "<script>alert('禁止直接访问');location='../';</script>";
//header("Location: login.php");

exit;
?>