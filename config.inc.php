<?php

class Config
{
	const dbhost = 'localhost';                 //数据库服务器地址
	const dbuser = 'root';                      //数据库用户名
	const dbpass = '123456';                    //数据库密码
	const dbname = 'www.jiaxiao.com';                //数据库名
	const table_pre = 'bccn_';                       //数据表前缀 /class/page.class.php 中用到。以下划线结尾，如：cdb_

	const debug = true;                         //是否开启调试模式
	const media = '/uploads/';                  //上传文件目录
}
