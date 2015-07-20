<?php
/**
 * 登出/注销
 *
 * @author MewX <imewx@qq.com>
 */

session_start();

// test is logged
if( isset($_SESSION['user_id']) )
	unset($_SESSION['user_id']);

// goto management page
header("Location: index.php"); // ==> ../index.php
exit;
?>
