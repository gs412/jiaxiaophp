<?php
namespace Admin;
use \Config as Config;
use \Db as Db;
use \File as File;
use \Page as Page;

/**
 * menu_name 文章管理
 * sort 1
 */
class ArticleController extends AdminBase
{

	protected $form_args = [
		'article_category_id' => ['label' => '文章分类', 'type' => 'select', 'options' => []],
		'title' => ['label'=>'标题', 'required'=>true],
		'author' => ['label' => '作者'],
		'sex' => ['label'=> '性别', 'type'=>'radio', 'options'=>[0=>'女', 1=>'男'], 'value'=>'1'],
		'like' => ['label'=>'爱好', 'type'=>'checkbox', 'options'=>[0=>'吃', 1=>'喝', 2=>'玩', 3=>'乐'], 'value'=>'1,3'],
		'from' => ['label' => '出处'],
		'pic' => ['label' => '封面图片', 'type' => 'images', 'max' => 4],
		'pic1' => ['label' => '单个图片', 'type' => 'image'],
		'rar' => ['label' => '相关附件', 'type' => 'files', 'max' => 3],
		'rar1' => ['label' => '单个附件', 'type' => 'file'],
		'description' => ['label'=>'简介', 'type'=>'textarea', 'required'=>false, 'style'=>'resize:none;'],
		'content' => ['label'=>'内容', 'type'=>'editor', 'required'=>true],
		'form_options' => [
			'form_title' => '添加文章',
			'index_title' => '文章列表',
			'per_page' => 10,
			'db_table' => 'article'
		]
	];

	/**
	 * menu_name 所有文章
	 * perm_name 浏览文章列表
	 * sort 10
	 */
	function index() {

		$this->index_render($this->form_args, function (&$form, &$articles) {
			unset($form['description']);
			unset($form['content']);
			$categories = DB_findall("select * from bccn_article_category");
			$new_categories = [];
			foreach ($categories as $category) {
				$new_categories[$category->id] = $category->name;
			}
			foreach ($articles as $article) {
				$article->article_category_id = $new_categories[$article->article_category_id];
				$article->title = '<a href="/Admin/Article/show/'.$article->id.'" target="_blank">'.cutstr($article->title, 50).'</a>';
			}
		});

	}

	/**
	 * perm_name 查看文章
	 * sort 15
	 */
	function show($id){

		//

	}

	/**
	 * menu_name 添加文章
	 * perm_name 添加文章
	 * sort 20
	 */
	function add() {
		if (REQUEST_METHOD == 'POST') {

			$this->form_create($this->form_args);
			redirect_to('/'.str_replace("#edit", "/index", $this->path_info));

		} else {

			$this->form_render($this->form_args, function (&$form) {
				$parents = DB_findall("select * from bccn_article_category ORDER BY sort");
				$form['article_category_id']['options'] = $this->get_options_val_and_text($parents);
			});

		}
	}

	/**
	 * perm_name 编辑文章
	 * sort 30
	 */
	function edit($id) {
		if (REQUEST_METHOD == 'POST') {

			$this->form_update($this->form_args, function (&$form) use ($id) {
				$form['id'] = $id;
			});
			redirect_to('/'.str_replace("#edit", "/index", $this->path_info));

		} else {
			$this->form_render($this->form_args, function (&$form) use ($id) {
				$parents = DB_findall("select * from bccn_article_category ORDER BY sort");
				$form['article_category_id']['options'] = $this->get_options_val_and_text($parents);
				$obj = DB_find("select * from ".Config::table_pre.$this->form_args['form_options']['db_table']." WHERE id=$id");
				foreach ($this->form_args as $name => $field) {
					$form[$name]['value'] = $obj->$name;
				}
			});
		}
	}

	/**
	 * perm_name 删除文章
	 * sort 40
	 */
	function delete($id) {
		parent::delete($id);
	}
}