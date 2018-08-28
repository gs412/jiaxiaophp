<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
define('ROOT', dirname(__DIR__).'/');
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
if (REQUEST_METHOD == 'GET') {
	define("request_method_is_GET", true);
	define("request_method_is_POST", false);
} elseif(REQUEST_METHOD == 'POST') {
	define("request_method_is_POST", true);
	define("request_method_is_GET", false);
}


foreach($_GET as $key412=>$value412){
	if(strlen($value412)>70){
		//file_put_contents("/var/www/bbs.bccn.net/web/too_long_querystring.log", "\n\n======================== too_long_querystring at ".date("Y-m-d H:i:s")." ========================\n", FILE_APPEND);
		//file_put_contents("/var/www/bbs.bccn.net/web/too_long_querystring.log", "\n".$_SERVER['REQUEST_URI']."\n\n".var_export($_GET, true)."\n\n", FILE_APPEND);
		die('too long querystring');
	}
}
unset($key412);
unset($value412);


require_once ROOT."config.inc.php";
require_once ROOT."include/mysql.class.php";
require_once ROOT."include/global.func.php";
require_once ROOT."include/global.array.php";

foreach(array('_COOKIE', '_POST', '_GET') as $_request){
	foreach($$_request as $_key => $_value){
		$_key{0} != '_' && $$_key = baddslashes($_value);
	}
}
//if(!MAGIC_QUOTES_GPC && $_FILES){
//	$_FILES = baddslashes($_FILES);
//}
define("REFERER", isset($referer) ? $referer : $_SERVER['HTTP_REFERER']);

$pdo = new PDO("mysql:host=".Config::dbhost.";dbname=".Config::dbname.";charset=utf8", Config::dbuser, Config::dbpass, [PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES utf8"]);
date_default_timezone_set('Asia/Shanghai');
$now = time();
$datetime = date("Y-m-d H:i:s", $now);
$uid = get_session('uid');
if($uid){
	$user = Db::find("select * from ".Config::table_pre."user where id='$uid'");
}
//$settings = Db::find("select * from settings");

#常用定义结束

function __autoload($class_name){
	$path1 = ROOT."include/class/".strtolower($class_name).".class.php";
	$path2 = ROOT."controller/".str_replace('\\', '/',$class_name).".php";
	if (is_file($path1)) {
		require_once $path1;
	} elseif (is_file($path2)) {
		require_once $path2;
	}
}