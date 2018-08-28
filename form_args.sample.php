<?php

// 必须是 protected $form_args ,以便让父类访问
$form_args = [
	'hidden_field' => ['type' => 'hidden', 'value' => '123'],
	'category_id' => ['type' => 'select', 'options'=>[0=>'女', 1=>'男'], 'value'=>'1'],
	'sex' => ['label'=> '性别', 'type'=>'radio', 'options'=>[0=>'女', 1=>'男'], 'value'=>'1'],
	'like' => ['label'=>'爱好', 'type'=>'checkbox', 'options'=>[0=>'吃', 1=>'喝', 2=>'玩', 3=>'乐'], 'value'=>'1,3'],
	// description中的style是可选项，如果想让其另起一行，可以使用display:block
	'title' => ['label'=>'标题', 'required'=>true, 'description' => ['text' => '最长30个字符', 'style' => 'display:block;']],
	'author' => ['label' => '作者'],
	'from' => ['label' => '出处', 'addition'=>'readonly style="color:red;"'], // 其实下面一行的那些项都可以用addition
	'pic' => ['label' => '封面图片', 'type' => 'images', 'max' => 3],   //max为最大上传数，超过这个数上传按钮隐藏，对image、file类型无效
	'pic1' => ['label' => '单个图片', 'type' => 'image'],
	'rar' => ['label' => '相关附件', 'type' => 'files'],
	'rar1' => ['label' => '单个附件', 'type' => 'file'],
	'description' => ['label'=>'简介', 'type'=>'textarea', 'required'=>true, 'style'=>'resize:none;'],
	// kindeditor 的初始化参数需要在 $this->add_page_style(file) 的 file中设置 window.KindEditor_args = {}
	'content' => ['label'=>'内容', 'type'=>'editor'],
	'password' => ['label'=>'密码', 'required'=>true],    // 键值包含字符串password的，input自动增加type=password
	'password_confirm' => ['label' => '密码确认', 'nodb' => true],  // 含有nodb键的，不创建数据库
	'form_options' => [
		'form_title' => '添加文章',
		'index_title' => '文章列表',
		'per_page' => 10,
		'db_table' => 'article'
	]
];

/**
 * 后台开发相关：
 *
 * 内核数据表以xxx_core_为开头
 * 可以在调用 $this->form_render 、 $this->index_render 前可使用 $this->add_page_style 、 $this->add_page_script 给页面设置自定义style和script
 * 继承 AdminBase 的后台控制器可以直接调用父类的 index、add、edit、delete方法，不过得显式调用，因为得设置menu_name和perm_name
 * $this->index_render 的时候，对于不想显示的列在回调函数中善用unset，比如 unset($form['description']);
 */