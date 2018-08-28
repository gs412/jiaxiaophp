<?php
namespace Admin;
use \Config as Config;
use \Db as Db;
use \File as File;
use \Page as Page;

class IndexController extends AdminBase
{
	function index() {
		include ROOT."template/Admin/Index/index.php";
	}

	function login() {
		if (REQUEST_METHOD == 'POST') {

			$username = post('username');
			$password = post('password');

			$user = DB_find("select * from bccn_core_admin_user where username='$username'");
			if ($user->password == md5($password)) {
				set_session('admin_uid', $user->id);
				redirect_to('/Admin/Index/');
			} else {
				set_session('msg', '用户名或密码错误');
				redirect_to('/Admin/Index/login');
			}

		} elseif (REQUEST_METHOD == 'GET') {

			$this->page_title = '后台登陆';

			if ($this->admin_uid) {
				redirect_to('/Admin/Index/');
			}

			include ROOT."template/Admin/Index/login.php";

		}
	}

	function logout() {
		del_session('admin_uid');
		redirect_to('/Admin/Index/login');
	}

	function top() {
		include ROOT."template/Admin/Index/top.php";
	}

	function left() {
		$controllers = AdminBase::get_controller_list();

		if ($this->admin_perm == 'all') {
			$perm_controllers = [];
		} else {
			$perm_controllers = array_map(function ($perm) {
				preg_match("/Admin\/(.*)#/is", $perm, $out);
				return $out[1];
			}, $this->admin_perm);
		}
		$controllers = array_filter($controllers, function ($controller) use ($perm_controllers) {
			return $controller->menu_name and ($this->admin->group_id==1 or in_array($controller->name, $perm_controllers));
		});

		$controllers = array_map(function ($controller) {
			$controller->methods = array_filter($controller->methods, function ($method) use ($controller) {
				return $method->menu_name and ($this->admin->group_id==1 or in_array("Admin/{$controller->name}#{$method->name}", $this->admin_perm));
			});
			return $controller;
		}, $controllers);

		include ROOT."template/Admin/Index/left.php";
	}

	function right() {
		include ROOT."template/Admin/Index/right.php";
	}

	function syncdb()
	{
		if (REQUEST_METHOD == 'POST') {
			$table_name = Config::table_pre.post('table_name');
			$fields = post('fields');
			$fields = array_merge(['id'=>'INT'], $fields);
			$fields = array_merge($fields, ['created_at'=>'DATETIME']);

			$old_fields = DB_table(post('table_name'))->get_db_fields();
			$old_fields = array_column_php7($old_fields, 'type', 'name');

			$new_fields = array_diff_key($fields, $old_fields);

			$map = [
				'(INT)' => "\\1(11) unsigned NOT NULL DEFAULT '0'",
				'(TINYINT)' => "\\1(4) unsigned NOT NULL DEFAULT '0'",
				'(VARCHAR\(\d+\))' => "\\1 NOT NULL DEFAULT ''",
				'(TEXT)' => "\\1 NOT NULL",
				'(DATETIME)' => "\\1 NOT NULL"
			];

			$new_fields = array_map(function ($field, $type) use ($map) {
				foreach ($map as $key => $value) {
					$reg = "/^$key$/is";
					if (preg_match($reg, $type)) {
						$type = preg_replace($reg, $value, $type);
					}
					if ($field == 'id') {
						$type = "INT(10) unsigned PRIMARY KEY NOT NULL AUTO_INCREMENT";
					}
				}
				return "`$field` $type";
			}, array_keys($new_fields), $new_fields);

			if ($old_fields) {
				$new_fields = array_map(function ($field) {
					return 'Add '.$field;
				}, $new_fields);
				$sql = "ALTER TABLE `$table_name` ".implode(',',$new_fields).";";
			} else {
				$sql = "CREATE TABLE IF NOT EXISTS `$table_name` ( ".implode(',',$new_fields)." ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			}

			$result = DB_query($sql);

			echo 'ok';
		}
	}

	function dropfield()
	{
		if (REQUEST_METHOD == 'POST') {
			$table_name = Config::table_pre.post('table_name');
			$field = post('field');
			$result = DB_query("ALTER TABLE `$table_name` DROP `$field`;");

			echo 'ok';
		}
	}

	//下面这三个都是相关图片相关附件需要的功能
	function upload_file()
	{
		$owner_type = post('owner_type');
		$owner_id = post('owner_id');
		$type = post('type');

		if (in_array($type, ['image', 'file'])) {
			$old_file = DB_find("select * from bccn_file WHERE user_id='$this->admin_uid' and owner_type='$owner_type' and owner_id='$owner_id'");
			$old_file = new File($old_file);
			$old_file->delete();
		}

		$file = new File();
		$file->set_user_id($this->admin_uid);
		$file->set_owner_id($owner_id);
		$file->set_owner_type($owner_type);
		$file->upload($_FILES['file2']);

		$file_id = $file->get_id();
		$file_url = $file->get_url();
		echo json_encode(['id'=>$file_id, 'url'=>$file_url]);
	}

	function get_file_list()
	{
		$form_action = post('form_action');
		$owner_type = post('owner_type');
		$owner_id = post('owner_id');

		if ($form_action == 'add') {
			$files = DB_findall("select id, `name`, path from bccn_file WHERE user_id='$this->admin_uid' and owner_type='$owner_type' and owner_id='0' ORDER BY id ASC");
		} else {
			$files = DB_findall("select id, `name`, path from bccn_file WHERE user_id='$this->admin_uid' and owner_type='$owner_type' and owner_id='$owner_id' ORDER BY id ASC");
		}
		foreach ($files as &$file) {
			$file->url = path_join(Config::media, $file->path);
			unset($file->path);
		}
		unset($file);

		echo json_encode($files);
	}

	function delete_file()
	{
		$file_id = post('file_id');
		$file = new File($file_id);
		$file->delete();
		echo 'ok';
	}

	//下面的是多功能编辑器需要的功能
	function editor_upload_file()
	{
		$owner_type = get('owner_type');
		$owner_id = get("owner_id");

		$file = new File();
		$file->set_user_id($this->admin_uid);
		$file->set_owner_id($owner_id);
		$file->set_owner_type($owner_type);
		$file->upload($_FILES['imgFile']);

		$file_url = $file->get_url();
		echo json_encode(['error'=>0, 'url'=>$file_url]);
	}
}