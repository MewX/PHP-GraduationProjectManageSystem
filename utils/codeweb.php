<?php
/**
 * 生成验证码的指令
 *
 * @author MewX <imewx@qq.com>
 */
session_start();
require_once('checkcode.php');

$checkcode=new CheckCode();
$checkcode->width=140;
$checkcode->height=40;
$checkcode->font_size=24;
$checkcode->doimage();
$_SESSION['code']=$checkcode->get_code();
?>