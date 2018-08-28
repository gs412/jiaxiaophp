<?php
namespace Admin;
use \Config as Config;
use \Db as Db;
use \File as File;
use \Page as Page;

/**
 * menu_name 文章分类管理
 * sort 5
 */
class ArticleCategoryController extends AdminBase
{

	protected $form_args = [
		'name' => ['label' => '分类名称', 'required' => true],
		'sort' => ['label' => '排序', 'required' => true, 'description' => ['text' => '0-225之间，数字越大越靠后']],
		'form_options' => [
			'form_title' => '添加文章分类',
			'index_title' => '文章分类列表',
			'per_page' => 100,
			'db_table' => 'article_category'
		]
	];

	/**
	 * menu_name 所有分类
	 * perm_name 浏览文章分类
	 * sort 10
	 */
	function index()
	{
		$this->index_render($this->form_args, function (&$form, &$categories) {
			unset($form['id']);
			usort($categories, function ($a, $b) {
				if ($a->sort == $b->sort) {
					return 0;
				}
				return ($a->sort < $b->sort) ? -1 : 1;
			});
		});
	}

	/**
	 * menu_name 添加分类
	 * perm_name 添加分类
	 * sort 20
	 */
	function add()
	{
		parent::add();
	}

	/**
	 * perm_name 编辑文章分类
	 * sort 30
	 */
	function edit($id)
	{
		parent::edit($id);
	}

	/**
	 * perm_name 删除文章分类
	 * sort 40
	 */
	function delete($id)
	{
		parent::delete($id);
	}
}