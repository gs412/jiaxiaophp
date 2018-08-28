<?php
include "include/common.inc.php";

$manage_action = get('manage_action');
if ($manage_action == 'create_controller_or_action' and Config::debug) {
	call_user_func(function () {
		$controller_path = get('controller_path');
		$controller_name = get('controller_name');
		$action_name = get('action_name');
		$notemp = get('notemp');
		$notemp = $notemp ? $notemp : 0;

		create_controller($controller_path, $controller_name, $action_name);
		if ($controller_path == 'Admin' and $notemp and has_str($action_name, 'index_add_edit_delete')) {
			$action_name_arr = array_unique(explode("_", $action_name));
			foreach ($action_name_arr as $action_name) {
				create_action($controller_path, $controller_name, $action_name, $notemp);
			}
			$action_name = 'index'; //为下面的 check_if_created 准备，否则最后一个delete带$id会报错
		} else {
			create_action($controller_path, $controller_name, $action_name, $notemp);
		}
		$request_path = path_join('/', $controller_path, str_replace('Controller', '', $controller_name), $action_name);
		if ($request_path == '/Index/index') {
			?><script>setTimeout(function(){window.location.href='/?manage_action=check_if_created';},50)</script><?
		} else {
			?><script>setTimeout(function(){window.location.href='<?= $request_path ?>?manage_action=check_if_created';},50)</script><?
		}
	});
	die;
}

$path_info = $_SERVER['PATH_INFO'];
if (!$path_info) {
	$path_info = explode('?',$_SERVER['REQUEST_URI'])[0];
}
$path_info = str_replace('index.php', '', $path_info);
//if ($path_info != '' and !ends_with($path_info, '/')) {
//	redirect_to($path_info.'/');
//}
if (!$path_info) {
	$path_info = get_safe('s');
}
$path_info = trim($path_info, '/');
$path_info = explode('/', $path_info);
$parameters = [];
if ($path_info == ['']) {
	define('CONTROLLER_PATH', '');
	define('CONTROLLER_NAME', "IndexController");
	define('ACTION_NAME', 'index');
} else {
	$contrl_path = [];
	$contrl_path = implode('/', $path_info);
	$contrl_path = preg_split("/\/([a-z].*$)/", $contrl_path, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

	$contrl_path_0 = array_reverse(explode('/', $contrl_path[0]));
	define('CONTROLLER_PATH', implode('/',array_reverse(array_slice($contrl_path_0, 1))));
	define('CONTROLLER_NAME', $contrl_path_0[0].'Controller');

	if (isset($contrl_path[1])) {
		$actions = explode('/', $contrl_path[1]);
		define('ACTION_NAME', $actions[0]);
		$parameters = array_slice($actions, 1);
	} else {
		define('ACTION_NAME', 'index');
	}
}

$action_name_safe = action_name_safe(ACTION_NAME);
if (Config::debug) {
	$controller_file = path_join(ROOT, 'controller', CONTROLLER_PATH, CONTROLLER_NAME).'.php';
	if (is_file($controller_file)) {
		include $controller_file;
	} else {
		ask_if_create(CONTROLLER_PATH, CONTROLLER_NAME, ACTION_NAME, 'controller');
	}
	$class = str_replace('/', "\\", CONTROLLER_PATH).'\\'.CONTROLLER_NAME;
	$controller = new $class();
	if (method_exists($controller, $action_name_safe)) {
		if (get('manage_action') == 'check_if_created') {
			if (CONTROLLER_PATH) {
				redirect_to('/'.CONTROLLER_PATH.'/'.str_replace('Controller', '', CONTROLLER_NAME).'/'.ACTION_NAME);
			} else {
				redirect_to('/'.str_replace('Controller', '', CONTROLLER_NAME).'/'.ACTION_NAME);
			}
		}
		$r = new ReflectionMethod($class, $action_name_safe);
		if (count($r->getParameters()) == count($parameters)) {
			call_user_func_array([$controller, $action_name_safe], $parameters);
		} else {
			echo 'number of parameters is error';
		}
	} else {
		ask_if_create(CONTROLLER_PATH, CONTROLLER_NAME, ACTION_NAME, 'action');
	}
} else {
	$controller_file = path_join(ROOT, 'controller', CONTROLLER_PATH, CONTROLLER_NAME).'.php';
	if (is_file($controller_file)) {
		include $controller_file;
	} else {
		page_not_found();
	}
	$class = str_replace('/', "\\", CONTROLLER_PATH).'\\'.CONTROLLER_NAME;
	$controller = new $class();
	if (method_exists($class, $action_name_safe)) {
		$r = new ReflectionMethod($class, $action_name_safe);
		if (count($r->getParameters()) == count($parameters)) {
			call_user_func_array([$controller, $action_name_safe], $parameters);
		} else {
			echo 'number of parameters is error';
		}
	} else {
		page_not_found();
	}
}

$pdo = null;    //关闭数据库连接







function ask_if_create($controller_path, $controller_name, $action_name, $type) {
	if (get('manage_action') == 'check_if_created') {
		redirect_to(REFERER);
	}
	$controller_file = path_join('controller', $controller_path, $controller_name).'.php';
	if ($type == 'controller') {
		echo "文件 $controller_file 不存在<br>";
	}
	echo "视图 $controller_file#$action_name 不存在<br>需要创建吗？<br>";
	$url = "/index.php?manage_action=create_controller_or_action&controller_path=$controller_path&controller_name=$controller_name&action_name=$action_name";
	echo "<a href='$url'>点此创建</a>";
	echo "<a href='$url&notemp=1' style='font-size:10px; text-decoration:none; color:#999; margin-left:260px;'>无模板创建</a>";
	if ($controller_path == 'Admin') {
		echo "<a href='{$url}_index_add_edit_delete&notemp=1' style='font-size:10px; text-decoration:none; color:#888; margin-left:60px;'>无模板创建CURD</a>";
	}
	die;
}

function create_controller($controller_path, $controller_name, $action_name) {
	$controller_file = path_join(ROOT, 'controller', $controller_path);
	if (!is_dir($controller_file)) {
		mkdir($controller_file, 0777, true);
	}
	$file = path_join($controller_file, $controller_name.'.php');
	if (!is_file($file)) {
		$namespace = str_replace('/', '\\', $controller_path);
		if ($namespace) {
			$define_namespace = <<<PHP
namespace $namespace;
use \Config as Config;
use \Db as Db;
use \File as File;
use \Page as Page;

PHP;
		} else {
			$define_namespace = '';
		}
		if ($namespace == 'Admin') {
			$last_sort = \Admin\AdminBase::get_last_controller_sort();
			$sort = (int)(round($last_sort/10)+1)*10;
			$docComment = <<<PHP
/**
 * menu_name $controller_name
 * sort $sort
 */

PHP;
			$extends = ' extends AdminBase';
			$table_name = ltrim(preg_replace_callback("/([A-Z])/s", function($match){return '_'.strtolower($match[1]);}, str_replace('Controller', '', $controller_name)), '_');
			$form_args = <<<PHP


	protected \$form_args = [
		'title' => ['label'=>'标题', 'required'=>true],
		'form_options' => [
			'form_title' => '添加XX',
			'index_title' => 'XX列表',
			'per_page' => 10,
			'db_table' => '$table_name'
		]
	];

PHP;
;
		} else {
			$docComment = '';
			$extends = '';
			$form_args = '';
		}
		file_put_contents($file, <<<PHP
<?php
$define_namespace
{$docComment}class {$controller_name}$extends
{{$form_args}
}
PHP
		);
	}
}

function create_action($controller_path, $controller_name, $action_name, $notemp)
{
	//生成模板相关
	if (!$notemp) {
		$template_path = path_join('template', $controller_path, str_replace('Controller', '', $controller_name));
		if (!is_dir(path_join(ROOT, $template_path))) {
			mkdir(path_join(ROOT, $template_path), 0777, true);
		}

		$template_name = path_join(ROOT, $template_path, $action_name.'.php');
		if (!is_file($template_name)) {
			$template_controller_and_action = path_join($controller_path, str_replace('Controller', '', $controller_name))."#$action_name";
			if (in_array($controller_path, ['','Admin'])) {
				$dot_and_slashes_repeat = '';
			} else {
				$dot_and_slashes_repeat = str_repeat("../", substr_count($controller_path, "/")+1);
			}
			$template_content = <<<PHP
<? include __DIR__.'/../{$dot_and_slashes_repeat}_/head.php'; ?>

$template_controller_and_action

<? include __DIR__.'/../{$dot_and_slashes_repeat}_/foot.php'; ?>
PHP;
			file_put_contents($template_name, $template_content);
		}
		$bottom_line = 'include ROOT.'."\"$template_path/$action_name.php\"";
	} else {
		if ($controller_path == 'Admin' and in_array($action_name, ['index','add','edit','delete'])) {
			$id = in_array($action_name,['edit','delete']) ? '$id' : '';
			$bottom_line = "parent::{$action_name}($id)";
		} else {
			$bottom_line = "echo \"无模板 ".path_join($controller_path, str_replace('Controller', '', $controller_name))."#$action_name\"";
		}
	}

	//生成action相关
	$controller_file = path_join(ROOT, 'controller', $controller_path, $controller_name).'.php';
	$str = file_get_contents($controller_file);
	$action_name_safe = action_name_safe($action_name);
	if (!preg_match("/\n\s*(public|protected|private)?\s*function\s*{$action_name_safe}\(/i", $str)) {
		$newline = strpos($str, 'function')===false ? "" : "\n";
		$id = '';
		if ($controller_path == 'Admin') {
			$last_sort = \Admin\AdminBase::get_last_action_sort('Admin\\'.$controller_name);
			$sort = ['index'=>10,'add'=>20,'edit'=>30,'delete'=>40][$action_name];
			$sort = $sort ? $sort : (int)(round($last_sort/10)+1)*10;
			if (in_array($action_name, ['index','add'])) {
				$perm_name = $action_name=='index' ? '所有XX' : '添加XX';
				$docComment = <<<PHP
	/**
	 * perm_name $perm_name
	 * menu_name $perm_name
	 * sort $sort
	 */

PHP;
			} else {
				$docComment = <<<PHP
	/**
	 * perm_name $action_name
	 * sort $sort
	 */

PHP;
				if (in_array($action_name, ['edit','delete'])) {
					$id = '$id';
				}
			}
		} else {
			$docComment = '';
		}
		$action_str = <<<PHP
{$newline}{$docComment}	function $action_name_safe($id)
	{
		$bottom_line;
	}
}
PHP;
		$str = preg_replace("/}\s*$/is", $action_str, $str);
		file_put_contents($controller_file, $str);
	}

}

function action_name_safe($action_name)
{
	if (in_array($action_name, ['list', 'use'])) {
		$action_name_safe = $action_name.'___bccn';
	} else {
		$action_name_safe = $action_name;
	}
	return $action_name_safe;
}