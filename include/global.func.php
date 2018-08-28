<?php

function alert($str)
{
	header("Content-type: text/html; charset=utf-8");
	?>
	<script>
		alert('<?= $str ?>');
	</script>
	<?
	die;
}

function alert_and_back($str)
{
	header("Content-type: text/html; charset=utf-8");
	?>
	<script>
		alert('<?= $str ?>');
		window.history.go(-1);
	</script>
	<?
	die;
}

function array_column_php7($array, $colum, $key='null')
{
	if ((int)substr(PHP_VERSION, 0, 1) >= 7) {
		return array_column($array, $colum, $key);
	} else {
		$new_array = [];
		foreach ($array as $a) {
			if (gettype($a) == 'array') {
				$k = $a[$key];
				if (isset($a[$colum])) {
					$v = $a[$colum];
				} else {
					return [];
				}
			} else {
				$k = $a->$key;
				if (isset($a->$colum)) {
					$v = $a->$colum;
				} else {
					return [];
				}
			}
			if ($key!='null' and $k) {
				$new_array[$k] = $v;
			} else {
				$new_array[] = $v;
			}
		}
		return $new_array;
	}
}

function baddslashes($string, $force = 0) {
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(!MAGIC_QUOTES_GPC || $force) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = baddslashes($val, $force);
			}
		} else {
			$string = addslashes($string);
		}
	}
	return $string;
}


/**
 * 字符串截取
 * 汉字占用两个字母宽度
 * @param $str
 * @param $len
 * @param string $dot
 * @return string
 */
function cutstr($str,$len, $dot='...'){
	$oldstr=$str;
	for($i=0;$i<$len;$i++){
		$temp_str=substr($str,0,1);
		if(ord($temp_str) > 127){
			$i++;
			if($i<$len){
				$new_str[]=substr($str,0,3);
				$str=substr($str,3);
			}
		}else{
			$new_str[]=substr($str,0,1);
			$str=substr($str,1);
		}
	}
	if(strlen(implode($new_str))==strlen($oldstr)){
		return implode($new_str);
	}else{
		return implode($new_str).$dot;
	}
}


function DB_query($sql, $array=array())
{
	return Db::query($sql, $array);
}
function DB_get($sql, $array=array())
{
	return Db::get($sql, $array);
}
function DB_getall($sql, $array=array())
{
	return Db::getall($sql, $array);
}
function DB_find($sql, $array=array())
{
	return Db::find($sql, $array);
}
function DB_findall($sql, $array=array())
{
	return Db::findall($sql, $array);
}
function DB_table($table_name)
{
	return Db::table($table_name);
}


function json_response($arr_or_str)
{
	header('content-type:application/json;charset=utf8');
	if (is_array($arr_or_str) or is_object($arr_or_str)) {
		echo json_encode((array)$arr_or_str);
	} else {
		echo $arr_or_str;
	}
}

function msg($str, $history_back=true){
	global $user, $uid;

	$title = '提示';
	@include ROOT."_head.php";
	?>
	<div style="background:#fff;border-radius:4px;margin:100px auto;padding:30px;width:400px;min-height:300px;box-sizing:border-box;box-shadow:0 0 4px 0 rgba(0, 0, 0, 0.08), 0 2px 4px 0 rgba(0, 0, 0, 0.12);">
		<div style="font-size:15px; margin-bottom:30px;"><?= $str ?></div>
		<? if($history_back): ?>
			<a href="###" onclick="history.back();" style="font-size:12px;">点此返回上一页</a>
		<? endif; ?>
	</div>
	<?
	@include ROOT."_foot.php";
	die();
}

function has_any($container, $array)
{
	$result = false;
	foreach ($array as $needle) {
		if (stripos($container, $needle) !== false) {
			$result = true;
		}
	}
	return $result;
}

function has_str($container, $needle)
{
	return stripos($container, $needle) !== false;
}

function get_page_num($total, $perpage)
{
	$page = $_GET['page'];
	if(!preg_match("/^\\d+$/", $page)){
		$page = 1;
	}
	return max(min(ceil($total/$perpage),$page),1);
}

function get_page_str($total, $perpage, $page)
{
	$url = $_SERVER['REQUEST_URI'];
	if(has_str($url, "page=")){
		$url = preg_replace("/page=\\d*/is", 'page=<page_num>', $url);
	}else{
		if(has_str($url, '.php?')){
			$url = $url . "&page=<page_num>";
		}else{
			$url = $url . "?page=<page_num>";
		}
	}

	$page_count = ceil($total/$perpage);
	$array = array();
	if($page < 9 or $total < 12){
		$array = array_merge($array, range(1, $page));
	}else{
		$array = array_merge($array, range(1, 2));
		array_push($array, "dot");
		$array = array_merge($array, range(min($page-4, $page_count-8),$page));
	}
	if($page > $page_count-8 or $total < 12){
		if($page < $page_count){
			$array = array_merge($array, range($page+1 ,$page_count));
		}
	}else{
		$array = array_merge($array, range($page+1,max($page+4, 9)));
		array_push($array, "dot");
		$array = array_merge($array, range($page_count-1,$page_count));
	}

	$array = array_map(function($i)use($url, $page){
		if($i == 'dot'){
			return '<span class="dot">.....</span>';
		}elseif($i == $page){
			return "<span class='current'>$i</span>";
		}else{
			return  "<a href='".str_replace("<page_num>", $i, $url)."'>$i</a>";
		}
	}, $array);
	return join('', $array);
}


/**
 * 得到汉字拼音首字母
 * @param $str
 * @return string
 */
function getfirstchar($str){
	$firstchar_ord=ord(strtoupper($str{0}));
	if(is_numeric($str{0})){
		return 'E';
	}
	if(($firstchar_ord>=65 and $firstchar_ord<=91)or($firstchar_ord>=48 and $firstchar_ord<=57)){
		return $str{0};
	}
	$s=iconv("UTF-8","gb2312", $str);
	$asc=ord($s{0})*256+ord($s{1})-65536;
	if($asc>=-20319 and $asc<=-20284)return "A";
	if($asc>=-20283 and $asc<=-19776)return "B";
	if($asc>=-19775 and $asc<=-19219)return "C";
	if($asc>=-19218 and $asc<=-18711)return "D";
	if($asc>=-18710 and $asc<=-18527)return "E";
	if($asc>=-18526 and $asc<=-18240)return "F";
	if($asc>=-18239 and $asc<=-17923)return "G";
	if($asc>=-17922 and $asc<=-17418)return "H";
	if($asc>=-17417 and $asc<=-16475)return "J";
	if($asc>=-16474 and $asc<=-16213)return "K";
	if($asc>=-16212 and $asc<=-15641)return "L";
	if($asc>=-15640 and $asc<=-15166)return "M";
	if($asc>=-15165 and $asc<=-14923)return "N";
	if($asc>=-14922 and $asc<=-14915)return "O";
	if($asc>=-14914 and $asc<=-14631)return "P";
	if($asc>=-14630 and $asc<=-14150)return "Q";
	if($asc>=-14149 and $asc<=-14091)return "R";
	if($asc>=-14090 and $asc<=-13319)return "S";
	if($asc>=-13318 and $asc<=-12839)return "T";
	if($asc>=-12838 and $asc<=-12557)return "W";
	if($asc>=-12556 and $asc<=-11848)return "X";
	if($asc>=-11847 and $asc<=-11056)return "Y";
	if($asc>=-11055 and $asc<=-10247)return "Z";
	return 'V';
}


function make_tree($arr){
	if(!function_exists('make_tree1')){
		function make_tree1($arr, $parent_id=0){
			$new_arr = array();
			foreach($arr as $k=>$v){
				if($v->parent_id == $parent_id){
					$new_arr[] = $v;
					unset($arr[$k]);
				}
			}
			foreach($new_arr as &$a){
				$a->children = make_tree1($arr, $a->id);
			}
			return $new_arr;
		}
	}
	return make_tree1($arr);
}

function make_tree_with_namepre($arr)
{
	$arr = make_tree($arr);
	if (!function_exists('add_namepre1')) {
		function add_namepre1($arr, $prestr='') {
			$new_arr = array();
			foreach ($arr as $v) {
				if ($prestr) {
					if ($v == end($arr)) {
						$v->name = $prestr.'└─ '.$v->name;
					} else {
						$v->name = $prestr.'├─ '.$v->name;
					}
				}

				if ($prestr == '') {
					$prestr_for_children = '　 ';
				} else {
					if ($v == end($arr)) {
						$prestr_for_children = $prestr.'　　 ';
					} else {
						$prestr_for_children = $prestr.'│　 ';
					}
				}
				$v->children = add_namepre1($v->children, $prestr_for_children);

				$new_arr[] = $v;
			}
			return $new_arr;
		}
	}
	return add_namepre1($arr);
}

/**
 * @param $arr
 * @param int $depth，当$depth为0的时候表示不限制深度
 * @return string
 */
function make_option_tree_for_select($arr, $depth=0)
{
	$arr = make_tree_with_namepre($arr);
	if (!function_exists('make_options1')) {
		function make_options1($arr, $depth, $recursion_count=0, $ancestor_ids='') {
			$recursion_count++;
			$str = '';
			foreach ($arr as $v) {
				$str .= "<option value='{$v->id}' data-depth='{$recursion_count}' data-ancestor_ids='".ltrim($ancestor_ids,',')."'>{$v->name}</option>";
				if ($v->parent_id == 0) {
					$recursion_count = 1;
				}
				if ($depth==0 || $recursion_count<$depth) {
					$str .= make_options1($v->children, $depth, $recursion_count, $ancestor_ids.','.$v->id);
				}

			}
			return $str;
		}
	}
	return make_options1($arr, $depth);
}

function get_ancestors($arr, $e)
{
	$ancestors = array();
	foreach ($arr as $a) {
		if ($a->id == $e->parent_id) {
			$ancestors[] = $a;
			if ($a->parent_id != 0) {
				$ancestors = array_merge(get_ancestors($arr, $a), $ancestors);
			}
		}
	}
	return $ancestors;
}

function get_children($arr, $id){
	$children = array();
	foreach($arr as $a){
		if($a->id == $id){
			return $a->children;
		}else{
			$children = get_children($a->children, $id);
		}
	}
	return $children;
}

function get_children_ids($arr){
	$ids = array();
	foreach($arr as $a){
		$ids[] = $a->id;
		if($a->children){
			$ids = array_merge($ids, get_children_ids($a->children));
		}
	}
	return $ids;
}

function getUserIP()
{
	$client = @$_SERVER['HTTP_CLIENT_IP'];
	$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
	$remote = $_SERVER['REMOTE_ADDR'];
	if (filter_var($client, FILTER_VALIDATE_IP)) {
		$ip = $client;
	} elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
		$ip = $forward;
	} else {
		$ip = $remote;
	}
	return $ip;
}

function need_user_login(){
	global $uid;
	if(!$uid){
		redirect_to("/login.php");
	}
}

function path_join(){
	$args = func_get_args();
	$paths = array();
	foreach ($args as $arg) {
		$paths = array_merge($paths, (array)$arg);
	}
	$paths = array_map(function($p, $k){
		if($k == 0){
			return rtrim($p, "/");
		}else{
			return trim($p, "/");
		}
	}, $paths, array_keys($paths));
	$paths = array_filter($paths);  //祛除空元素
	$paths = join('/', $paths);
	if ($args[0] == '/') {
		$paths = '/'.$paths;
	}
	return $paths;
}

function get($str, $max_length=75){
	if (strlen($_GET[$str]) > $max_length) {
		echo "get参数“{$str}”长度超过了".$max_length;
		die();
	}
	return $_GET[$str];
}

function get_safe($str, $max_length=75){
	return baddslashes(get($str, $max_length));
}

function get_int($str){
	if (preg_match("/\d+/is", $_GET[$str], $out)) {
		return $out[0];
	} else {
		return '';
	}
}

function get_file($owner_id, $owner_type)
{
	$pic = DB_find("select * from bccn_file WHERE owner_id='$owner_id' and owner_type='$owner_type'");
	$file = new File($pic);
	return $file;
}

function post($str){
	return $_POST[$str];
}

function post_safe($str){
	return baddslashes(post($str));
}

function post_int($str) {
	if (preg_match("/\d+/is", $_POST[$str], $out)) {
		return $out[0];
	} else {
		return '';
	}
}

function page_not_found()
{
	header('HTTP/1.1 404 Not Found');
	header("status: 404 Not Found");
	echo "<html><title>404 Not Found</title><body><h1>404 Not Found</h1></body></html>";
	die;
}

function json_post($str)
{
	$json = file_get_contents('php://input');
	$obj = json_decode($json, true);
	return $obj[$str];
}

function redirect_to($url){
	header("Location: $url");
	die();
}

function request_get($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_REFERER, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function request_post($url = '', $post_data, $header=['content-type: application/x-www-form-urlencoded;charset=UTF-8']) {
	if (empty($url) || empty($post_data)) {
		return false;
	}
	$postUrl = $url;
	$curlPost = $post_data;
	$ch = curl_init();//初始化curl
	curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);  //设置头信息的地方
	curl_setopt($ch, CURLOPT_HEADER, 0);//不取得返回头信息
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
	curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	$data = curl_exec($ch);//运行curl
	curl_close($ch);

	return $data;
}

function starts_with($haystack, $needle)
{
	return $needle === "" || strpos($haystack, $needle) === 0;
}
function ends_with($haystack, $needle)
{
	return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

function __start_session($age=2592000){
	if(!defined('SESSION_START')){
		define('SESSION_START', true);
		session_set_cookie_params($age);
		session_start();
	}
}
function set_session($key, $value){
	__start_session();
	$_SESSION[$key] = $value;
}
function get_session($key){
	__start_session();
	return $_SESSION[$key];
}
function del_session($key){
	__start_session();
	unset($_SESSION[$key]);
}