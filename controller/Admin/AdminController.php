<?php
namespace Admin;
use \Config as Config;
use \Db as Db;
use \File as File;
use \Page as Page;

/**
 * menu_name 管理员管理
 * sort 30
 */
class AdminController extends AdminBase
{
	/**
	 * menu_name 管理员列表
	 * perm_name 浏览管理员列表
	 * sort 1
	 */
	function index()
	{
		$admins = DB_findall("select u.*,g.name as gname from bccn_core_admin_user as u
							  left join bccn_core_admin_group as g on g.id=u.group_id
							  order by g.sort asc, u.id asc");
		include ROOT."template/Admin/Admin/index.php";
	}

	/**
	 * perm_name 添加管理员
	 * sort 2
	 */
	function add()
	{
		$form_args = [
			'username' => ['label'=>'账号', 'required'=>true],
			'password' => ['label'=>'密码', 'required'=>true],
			'realname' => ['label'=>'姓名', 'required'=>true],
			'group_id' => ['type'=>'select', 'label'=>'权限组'],
			'form_options' => [
				'title' => '添加管理员',
				'db_table' => 'core_admin_user'
			]
		];

		if (REQUEST_METHOD == 'POST') {

			$username = post('username');
			if (DB_get("select count(*) from bccn_core_admin_user WHERE username='$username'")) {
				alert_and_back('用户名已经存在');
			} else {
				$this->form_create($form_args, function (&$form) {
					$form['password'] = md5(post('password'));
				});
				redirect_to('/Admin/Admin/index');
			}

		} else {
			$this->form_render($form_args, function (&$form) {

				$groups = DB_findall("select * from bccn_core_admin_group where id>1 order by sort,id");
				$form['group_id']['options'] = $this->get_options_val_and_text($groups);
				array_unshift($form['group_id']['options'], [1, '总管理员']);

			});
		}
	}

	/**
	 * perm_name 编辑管理员信息
	 * sort 3
	 */
	function edit($id)
	{
		$form_args = [
			'id' => ['value'=>$id, 'type'=>'hidden'],
			'username' => ['label'=>'账号', 'required'=>true],
			'password' => ['label'=>'密码', 'required'=>false, 'placeholder'=>'如果不修改请留空'],
			'realname' => ['label'=>'姓名', 'required'=>true],
			'group_id' => ['type'=>'select', 'label'=>'权限组'],
			'form_options' => [
				'title' => '编辑管理员',
				'db_table' => 'core_admin_user'
			]
		];

		if (REQUEST_METHOD == 'POST') {
			$this->form_update($form_args, function (&$form) {

				if (post('password')) {
					$form['password'] = md5(post('password'));
				} else {
					unset($form['password']);
				}

			});
			redirect_to('/Admin/Admin/index');
		} else {
			$this->form_render($form_args, function (&$form) use ($id) {

				$admin = DB_find("select * from bccn_core_admin_user where id='$id'");
				$form = (array)$admin;
				unset($form['password']);

				$groups = DB_findall("select * from bccn_core_admin_group where id>1 order by sort,id");
				$form['group_id'] = ['value'=>$form['group_id'], 'options'=> $this->get_options_val_and_text($groups)];
				array_unshift($form['group_id']['options'], [1, '总管理员']);

			});
		}
	}

	/**
	 * perm_name 删除管理员
	 * sort 4
	 */
	function delete($id)
	{
		if (REQUEST_METHOD == 'POST') {
			if ($id == $this->admin_uid) {
				set_session('msg', '不能删除自己');
				redirect_to(REFERER);
			}

			$admin = DB_table('core_admin_user')->find_by_id($id);
			if ($admin->group_id == 1 && DB_get("select count(*) from bccn_core_admin_user WHERE group_id='1' and id<>'$id'") == 0) {
				set_session('msg', '这是最后一个总管理员，总要留一个总管理员');
				redirect_to(REFERER);
			}

			DB_table('core_admin_user')->delete_by_id($id);

			redirect_to(REFERER);
		}
	}

	/**
	 * menu_name 权限组列表
	 * perm_name 浏览权限组列表
	 * sort 5
	 */
	function grouplist()
	{
		$groups = DB_findall("select * from bccn_core_admin_group where id>1 order by sort, id asc");

		include ROOT."template/Admin/Admin/grouplist.php";
	}

	/**
	 * perm_name 添加权限组
	 * sort 6
	 */
	function groupadd()
	{
		if (REQUEST_METHOD == 'POST') {

			$data = [
				'name' => post('name'),
				'sort' => post('sort')
			];
			DB_table('core_admin_group')->create($data);

			redirect_to('/Admin/Admin/grouplist');

		} else {

			include ROOT."template/Admin/Admin/groupadd.php";
		}
	}

	/**
	 * perm_name 编辑权限组
	 * sort 7
	 */
	function groupedit($id)
	{
		if ($id == 1) {
			set_session('msg', '内置管理员禁止编辑');
			redirect_to(REFERER);
		}

		if (REQUEST_METHOD == 'POST') {

			$data = [
				'name' => post('name'),
				'sort' => post('sort'),
				'id' => intval($id)
			];
			DB_table('core_admin_group')->update($data);

			redirect_to('/Admin/Admin/grouplist');

		} else {
			$group = DB_table('core_admin_group')->find_by_id($id);

			include ROOT."template/Admin/Admin/groupedit.php";
		}
	}

	/**
	 * perm_name 删除权限组
	 * sort 8
	 */
	function groupdelete($id)
	{
		if ($id == 1) {
			set_session('msg', '内置管理员禁止编辑');
			redirect_to(REFERER);
		}

		if (REQUEST_METHOD == 'POST') {
			DB_table('core_admin_group')->delete_by_id($id);

			redirect_to('/Admin/Admin/grouplist');
		}
	}

	/**
	 * perm_name 为权限组分配权限
	 * sort 9
	 */
	function group_setperm($id)
	{
		if ($id == 1) {
			set_session('msg', '内置管理员拥有所有权限，无需配置');
			redirect_to(REFERER);
		}

		if (REQUEST_METHOD == 'POST') {

			$data = [
				'perm' => implode(',', post('perm')),
				'id' => intval($id)
			];
			DB_table('core_admin_group')->update($data);
			set_session('msg', '配置权限成功');
			redirect_to(REFERER);

		} else {

			$group = DB_table('core_admin_group')->find_by_id($id);
			$controllers = AdminBase::get_controller_list();
			include ROOT."template/Admin/Admin/group_setperm.php";

		}
	}
}