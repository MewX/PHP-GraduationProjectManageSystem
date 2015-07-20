<?php
/**
 * 基础函数包
 *
 * @author MewX <imewx@qq.com>
 */

/* 数据库信息 */
global $server_sql_server;
global $server_sql_port;
global $server_sql_username;
global $server_sql_password;
global $server_sql_db;

/* LOCAL */
$server_sql_server = 'localhost';
$server_sql_port = '3306';
$server_sql_username = 'root';
$server_sql_password = 'root';
$server_sql_db = "GP";

/* SAE */
// 用户名　 : SAE_MYSQL_USER
// 密　　码 : SAE_MYSQL_PASS
// 主库域名 : SAE_MYSQL_HOST_M
// 从库域名 : SAE_MYSQL_HOST_S
// 端　　口 : SAE_MYSQL_PORT
// 数据库名 : SAE_MYSQL_DB
// $server_sql_server = SAE_MYSQL_HOST_M;
// $server_sql_port = SAE_MYSQL_PORT;
// $server_sql_username = SAE_MYSQL_USER;
// $server_sql_password = SAE_MYSQL_PASS;
// $server_sql_db = SAE_MYSQL_DB;


/* 数据库相关函数 */
// 连接数据库，返回数据库句柄
function connectDatabase()
{
    global $server_sql_server;
    global $server_sql_port;
    global $server_sql_username;
    global $server_sql_password;
    global $server_sql_db;

    $server = $server_sql_server;
    $port = $server_sql_port;
    $username = $server_sql_username;
    $password = $server_sql_password;
    $dbname = $server_sql_db;
	
    // 确保读取的是utf-8格式
    $pdo = new PDO("mysql:host=" . $server . ";port=" . $port . ";dbname=" . $dbname, $username, $password);
    $pdo->exec('set names utf8');
    return $pdo;
}

?>
