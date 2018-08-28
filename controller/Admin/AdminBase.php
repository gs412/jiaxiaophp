<?php
namespace Admin;
use \Config as Config;
use \Db as Db;
use \File as File;
use \Page as Page;

class AdminBase extends \CommonBase
{
	protected $form_args = [];
	private $page_styles = [];
	private $page_scripts = [];

	function __construct()
	{
		parent::__construct();
		$this->user = 'user';
		$this->page_title = '后台';
		$this->admin_uid = get_session('admin_uid');
		if (!$this->admin_uid and !in_array($this->path_info, ['Admin/Index#login'])) {
			redirect_to('/Admin/Index/login');
		}
		$this->admin = DB_find("SELECT * FROM bccn_core_admin_user WHERE id='{$this->admin_uid}'");

		if ($this->admin->group_id == 1) {
			$this->admin_perm = 'all';
		} else {
			$this->admin_perm = DB_get("select perm from bccn_core_admin_group WHERE id='{$this->admin->group_id}'");
			if ($this->admin_perm == 'all') {   //非内置组不可能拥有all权限
				$this->admin_perm = [];
			} else {
				$this->admin_perm = explode(',', $this->admin_perm);
			}
		}

		//检查权限
		if ($this->admin_perm != 'all' and !has_str($this->path_info, 'Admin/Index#') and !in_array(str_replace('#','Controller#',$this->path_info), $this->admin_perm)) {
			$this->admin_msg('您没有权限进行此操作');
		}
	}

	protected function admin_msg($str, $history_back=true)
	{
		header("Content-type: text/html; charset=utf-8");
		?>
		<style>
			a{color:#000; text-decoration:none; font-size:12px;}
			a:hover{color:#4455aa; text-decoration:underline;}
		</style>
		<table style="height:60%; width:100%;">
			<tr>
				<td style="text-align:center;">
					<div style="font-size:15px; margin-bottom:30px;"><?= $str ?></div>
					<? if($history_back): ?>
						<a href="javascript:history.go(-1);">点此返回上一页</a>
					<? endif; ?>
				</td>
			</tr>
		</table>
		<?
		die();
	}

	protected static function get_controller_list()
	{
		$controller_file_list = glob("controller/Admin/*.php");
		$controller_file_list = array_filter($controller_file_list, function ($controller) {
			return has_str($controller, 'Controller.php') and !has_any($controller, ['MenuController.php', 'IndexController.php']);
		});
		$controller_file_list = array_values($controller_file_list);
		$controller_list = array_map(function ($controller_file) {
			$controller_name = explode('.', end(explode('/', $controller_file)))[0];
			$class_name = 'Admin\\'.$controller_name;
			$controller = new $class_name();
			$r = new \ReflectionClass($controller);
			$docComment = $r->getDocComment();
			$obj = new \stdClass();
			$obj->name = $r->getShortName();
			$obj->menu_name = self::get_menu_name($docComment);
			$obj->perm_name = self::get_perm_name($docComment);
			$obj->sort = self::get_sort($docComment);
			$obj->methods = $r->getMethods(\ReflectionMethod::IS_PUBLIC);
			$obj->methods = array_filter($obj->methods, function ($method) use ($class_name) {
				return $method->class == $class_name;
			});
			$obj->methods = array_map(function ($method) use ($class_name) {
				$name = $method->name;
				$r = new \ReflectionMethod($class_name, $name);
				$docComment = $r->getDocComment();
				$obj = new \stdClass();
				$obj->name = $name;
				$obj->menu_name = self::get_menu_name($docComment);
				$obj->perm_name = self::get_perm_name($docComment);
				$obj->sort = self::get_sort($docComment);
				return $obj;
			},$obj->methods);
			usort($obj->methods, ['self', 'order_by_sort']);
			return $obj;
		}, $controller_file_list);

		usort($controller_list, ['self', 'order_by_sort']);

		return $controller_list;
	}

	public static function get_last_controller_sort()
	{
		$controllers = self::get_controller_list();
		$controllers = array_filter($controllers, function ($method) {
			return $method->sort < 10000;
		});
		usort($controllers, ['self', 'order_by_sort']);
		return (int)end($controllers)->sort;
	}

	public static function get_last_action_sort($class_name)
	{
		$controller = new $class_name();
		$r = new \ReflectionClass($controller);
		$methods = $r->getMethods();
		$methods = array_filter($methods, function ($method) use ($class_name) {
			return $method->class == $class_name;
		});
		$methods = array_map(function ($method) use ($class_name) {
			$r = new \ReflectionMethod($class_name, $method->name);
			$docComment = $r->getDocComment();
			$obj = new \stdClass();
			$obj->sort = self::get_sort($docComment);
			return $obj;
		}, $methods);
		$methods = array_filter($methods, function ($method) {
			return $method->sort < 10000;
		});
		usort($methods, ['self', 'order_by_sort']);
		return (int)end($methods)->sort;
	}

	protected function add_page_style($style_file_path)
	{
		$this->page_styles[] = $style_file_path;
	}

	protected function add_page_script($script_file_path)
	{
		$this->page_scripts[] = $script_file_path;
	}

	protected function form_render($args, $callback='')
	{
		$form_options = ['form_title' => '填写表单'];
		foreach ($args['form_options'] as $k => $v) {
			$form_options[$k] = $v;
		}

		$form_fields = $args;
		unset($form_fields['form_options']);
		foreach ($form_fields as $name => &$value) {
			$default_value = ['type'=>'text', 'value'=>'', 'required'=>false];
			if (has_str($name, 'password')) {
				$default_value['type'] = 'password';
			}
			foreach ($value as $k => $v) {
				$default_value[$k] = $v;
			}
			if (!isset($default_value['label'])) {
				$default_value['label'] = $name;
			}
			$value = $default_value;
		}
		unset($value);

		if ($callback) {
			$form = [];
			$callback($form);
			foreach ($form as $k=>$v) {
				if (!is_array($v)) {
					$v = ['value'=>$v];
				}
				foreach ($form_fields as $name => &$value) {
					if ($name == $k) {
						foreach ($v as $k1=>$v1) {
							$value[$k1] = $v1;
						}
					}
				}
				if ($k == 'form_options') {
					foreach ($v as $k2=>$v2) {
						if (array_key_exists($k2, $form_options)) {
							$form_options[$k2] = $v2;
						}
					}
				}
				unset($value);
			}
		}

		$db_fields = Db::table($args['form_options']['db_table'])->get_db_fields();
		$db_fields = array_column_php7($db_fields, 'type', 'name');
		$form_fields_need_db = array_filter($form_fields, function($field){
			if (in_array($field['type'], ['images', 'files', 'image', 'file'])) {
				return false;
			} elseif (isset($field['nodb'])) {
				return false;
			} else {
				return true;
			}
		});
		$lack_db_fields = array_diff_key($form_fields_need_db, $db_fields);
		$more_db_fields = array_diff_key($db_fields, $form_fields_need_db);
		$more_db_fields = array_diff_key($more_db_fields, ['id'=>null, 'created_at'=>null]);

		$db_fields_options = ['INT', 'TINYINT', 'VARCHAR(X)', 'VARCHAR(255)', 'VARCHAR(500)', 'VARCHAR(2000)', 'VARCHAR(5000)', 'TEXT', 'DATETIME'];
		foreach ($form_fields as $name => &$value) {
			$db_type = 'VARCHAR(500)';
			if ($value['type'] == 'textarea') {
				$db_type = 'VARCHAR(5000)';
			} elseif ($value['type'] == 'editor') {
				$db_type = 'TEXT';
			} elseif (in_array($value['type'], ['radio','select','checkbox'])) {
				$db_type = 'TINYINT';
			} elseif (ends_with($name, '_id')) {
				$db_type = 'INT';
			}
			$value['db_type'] = $db_type;
		}

		include ROOT."template/Admin/_/form_render.php";
	}

	protected function form_create($args, $callback='')
	{
		$form_fields = $args;
		$db_table = $form_fields['form_options']['db_table'];
		unset($form_fields['form_options']);
		$form = [];
		foreach ($form_fields as $name=>$field) {
			$form[$name] = post($name);
		}
		if ($callback) {
			$callback($form);
		}
		DB_table($args['form_options']['db_table'])->create($form);
		$id = Db::insert_id();
		foreach ($form_fields as $name=>$field) {
			if ($field['type'] == 'editor') {
				File::clean_useless_img(post($name), $db_table.'_'.$name, 0, $this->admin_uid);
			}
			if (in_array($field['type'], ['images','image','files','file','editor'])) {
				File::set_owner_id_for_newfile($db_table.'_'.$name, $id, $this->admin_uid);
			}
		}
		return $id;
	}

	protected function form_update($args, $callback='')
	{
		$form_fields = $args;
		$db_table = $form_fields['form_options']['db_table'];
		unset($form_fields['form_options']);
		$form = [];
		foreach ($form_fields as $name=>$field) {
			if (is_array(post($name))) {
				$form[$name] = implode(',',post($name));
			} else {
				$form[$name] = post($name);
			}
			if ($form[$name] == null) {
				$form[$name] = '';
			}
		}
		if ($callback) {
			$callback($form);
		}
		DB_table($args['form_options']['db_table'])->update($form);
		foreach ($form_fields as $name=>$field) {
			if ($field['type'] == 'editor') {
				File::clean_useless_img(post($name), $db_table.'_'.$name, $form['id'], $this->admin_uid);
			}
			if (in_array($field['type'], ['images','image','files','file','editor'])) {
				File::set_owner_id_for_newfile($db_table.'_'.$name, $form['id'], $this->admin_uid);
			}
		}
	}

	protected function index_render($args, $callback='')
	{
		$form = ['id'=>['label'=>'编号']];
		$form = array_merge($form, $args);
		$form = array_diff_key($form, ['form_options'=>null]);
		$form['created_at'] = ['label'=>'创建时间'];

		$page = new Page($args['form_options']['db_table']);
		if (isset($args['form_options']['sql'])) {
			$page->set_sql($args['form_options']['sql']);
		} else {
			if (isset($args['form_options']['where'])) {
				$page->set_where($args['form_options']['where']);
			}
			$page->set_order_by(isset($args['form_options']['order_by']) ? $args['form_options']['order_by'] : 'id desc');
		}
		if (isset($args['form_options']['total'])) {
			$page->set_total($args['form_options']['total']);
		}
		$page->set_perpage(isset($args['form_options']['per_page']) ? $args['form_options']['per_page'] : 10);

		$objects = $page->get_objects();
		$pagestr = $page->get_pagestr();

		if ($callback) {
			$callback($form, $objects);
		}

		foreach ($objects as &$object) {
			foreach ((array)$object as $k=>$v) {
				if (isset($form[$k]['type']) and in_array($form[$k]['type'], ['radio','select','checkbox']) and in_array($v, array_keys($form[$k]['options']))) {
					$object->$k = $form[$k]['options'][$v];
				}
			}
		}
		unset($object);

		$columns = array_keys($form);
		foreach ($columns as $column) {
			if (!isset($form[$column]['label'])) {
				$form[$column]['label'] = $column;
			}
		}

		include ROOT.'template/Admin/_/index_render.php';
	}

	protected function get_options_val_and_text($objects, $fields=['id','name'])
	{
		$array = [];
		foreach ($objects as $obj) {
			$array[] = [$obj->{$fields[0]}, $obj->{$fields[1]}];
		}
		return $array;
	}

	//下面是4个CURD方法，供子类调用，子类也可重写
	function index()
	{
		$this->index_render($this->form_args);
	}
	function add()
	{
		if (REQUEST_METHOD == 'POST') {

			$this->form_create($this->form_args);
			redirect_to('/'.str_replace("#edit", "/index", $this->path_info));

		} else {

			$this->form_render($this->form_args);

		}
	}
	function edit($id)
	{
		if (REQUEST_METHOD == 'POST') {

			$this->form_update($this->form_args, function (&$form) use ($id) {
				$form['id'] = $id;
			});
			redirect_to('/'.str_replace("#edit", "/index", $this->path_info));

		} else {
			if (starts_with($this->form_args['form_options']['form_title'], '添加')) {
				$this->form_args['form_options']['form_title'] = str_replace('添加', '编辑', $this->form_args['form_options']['form_title']);
			}
			$this->form_render($this->form_args, function (&$form) use ($id) {
				$obj = DB_find("select * from ".Config::table_pre.$this->form_args['form_options']['db_table']." WHERE id=$id");
				foreach ($this->form_args as $name => $field) {
					$form[$name]['value'] = $obj->$name;
				}
			});
		}
	}
	function delete($id)
	{
		$db_table = $this->form_args['form_options']['db_table'];
		foreach ($this->form_args as $name=>$field) {
			if (in_array($field['type'], ['images','image','files','file','editor'])) {
				$files = DB_findall("select * from bccn_file WHERE owner_type=? and owner_id=? and user_id=?", [$db_table.'_'.$name, $id, $this->admin_uid]);
				foreach ($files as $file) {
					$file = new File($file);
					$file->delete();
				}
			}
		}
		DB_query("delete from ".Config::table_pre.$this->form_args['form_options']['db_table']." WHERE id=$id");
		redirect_to(REFERER);
	}

	private static function get_perm_name($docComment)
	{
		preg_match("/\*\s+perm_name\s+(.*)\s*\n/i", $docComment, $out);
		return $out[1];
	}

	private static function get_menu_name($docComment)
	{
		preg_match("/\*\s+menu_name\s+(.*)\s*\n/i", $docComment, $out);
		return $out[1];
	}

	private static function get_sort($docComment)
	{
		preg_match("/\*\s+sort\s+(.*)\s*\n/i", $docComment, $out);
		$sort = $out[1];
		if (!$sort) {
			$sort = 99999999;
		}
		return $sort;
	}

	private static function order_by_sort($a, $b) {
		$a = $a->sort;
		$b = $b->sort;
		if ($a == $b) {
			return 0;
		}
		return $a > $b ? 1 : -1;
	}
}
