<?php

$form_args = [
	'title' => ['label'=>'标题', 'type'=>'text', 'value'=>'文章标题1', 'required'=>true, 'style'=>'font-size:12px;', 'addition'=>'readonly style="color:red;"'],
	'author' => ['label' => '作者'],
	'from' => ['label' => '出处'],
	'description' => ['label'=>'简介', 'type'=>'textarea', 'required'=>true, 'style'=>'resize:none;'],
	'form_options' => [
		'form_title' => '添加文章',
		'index_title' => '文章列表',
		'per_page' => 10,
		'order_by' => 'id desc',
		'db_table' => 'article'
	]
];