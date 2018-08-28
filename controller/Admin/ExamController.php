<?php
namespace Admin;
use \Config as Config;
use \Db as Db;
use \File as File;
use \Page as Page;

/**
 * menu_name 考试管理
 * sort 20
 */
class ExamController extends AdminBase
{
	/**
	 * menu_name 考试列表
	 * perm_name 浏览所有考试
	 * sort 1
	 */
	function index() {
		include ROOT."template/Admin/Exam/index.php";
	}

	/**
	 * menu_name 考试添加
	 * perm_name 添加考试
	 * sort 2
	 */
	function add() {
		include ROOT."template/Admin/Exam/add.php";
	}
}