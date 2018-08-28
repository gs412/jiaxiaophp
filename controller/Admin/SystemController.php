<?php
namespace Admin;
use \Config as Config;
use \Db as Db;
use \File as File;
use \Page as Page;

/**
 * menu_name 系统设置
 * sort 40
 */
class SystemController extends AdminBase
{
	/**
	 * menu_name 基本设置
	 * perm_name 基本设置
	 * sort 1
	 */
	function basic()
	{
		include ROOT."template/Admin/System/basic.php";
	}
}