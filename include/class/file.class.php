<?php
require_once ROOT . "include/simple_html_dom.php";

class File{

	private $name = null;
	private $path = null;
	private $size = null;
	private $owner_type = null;
	private $owner_id = null;
	private $user_id = null;
	private $created_at = null;

	private $id = 0;
	private $_sqlsnnipet = '';
	private $_sqlsnnipet_keys = [];

	public function __construct($obj_or_id=0){
		global $now;
		$this->now = $now;

		if($obj_or_id){
			if(in_array(gettype($obj_or_id), array('integer', 'string'))){
				$this->id = $obj_or_id;
				$file = Db::find("select * from ".Config::table_pre."file where id='$this->id'");
				if ($file == null) {
					$this->set_id(0);
				}
			}else{
				$this->id = $obj_or_id->id;
				$file = $obj_or_id;
			}
			$this->name = $file->name;
			$this->path = $file->path;
			$this->size = $file->size;
			$this->owner_type = $file->owner_type;
			$this->owner_id = $file->owner_id;
			$this->user_id = $file->user_id;
			$this->created_at = $file->created_at;
		}
	}

	/**
	 * 没用到的图片附件清理掉
	 * @param $content
	 * @param $owner_type
	 * @param $owner_id
	 * @param $user_id
	 */
	public static function clean_useless_img($content, $owner_type, $owner_id, $user_id){
		//提取img的src
		$html = new simple_html_dom($content.'<br>');
		$imgs = $html->find("img");
		$srcs = array_map(function($x){
			$src = $x->attr['src'];
			$src = trim($src, '"');
			$src = trim($src, "\\");
			$src = trim($src, '"');
			$src = str_replace("http://".$_SERVER['HTTP_HOST'], '', $src);
			return $src;
		}, $imgs);
		$srcs = array_filter($srcs, function($x){
			return starts_with($x, Config::media);
		});

		//不在$srcs中的附件删掉
		if($owner_id == 0){
			$owner_ids = "'0'";
		}else{
			$owner_ids = "'0', '$owner_id'";
		}
		$files = Db::findall("select * from ".Config::table_pre."file where owner_type='$owner_type' and owner_id in($owner_ids) and user_id='$user_id'");
		foreach($files as $file){
			$file = new File($file);
			if(!in_array($file->get_url(), $srcs)){
				$file->delete();
			}
		}
	}

	public static function set_owner_id_for_newfile($owner_type, $owner_id, $user_id){
		Db::query("update ".Config::table_pre."file set owner_id='$owner_id' where owner_type='$owner_type' and owner_id='0' and user_id='$user_id'");
	}

	public function set_id($id){
		$this->id = $id;
	}
	public function set_name($name){
		$this->name = $name;
		if (!in_array('name', $this->_sqlsnnipet_keys)) {
			$this->_sqlsnnipet_keys[] = 'name';
			$this->_sqlsnnipet .= "name='$name',";
		}
	}
	public function set_path($path){
		$this->path = $path;
		if (!in_array('path', $this->_sqlsnnipet_keys)) {
			$this->_sqlsnnipet_keys[] = 'path';
			$this->_sqlsnnipet .= "path='$path',";
		}
	}
	public function set_size($size){
		$this->size = $size;
		if (!in_array('size', $this->_sqlsnnipet_keys)) {
			$this->_sqlsnnipet_keys[] = 'size';
			$this->_sqlsnnipet .= "size='$size',";
		}
	}
	public function set_owner_type($owner_type){
		$this->owner_type = $owner_type;
		if (!in_array('owner_type', $this->_sqlsnnipet_keys)) {
			$this->_sqlsnnipet_keys[] = 'owner_type';
			$this->_sqlsnnipet .= "owner_type='$owner_type',";
		}
	}
	public function set_owner_id($owner_id){
		$this->owner_id = $owner_id;
		if (!in_array('owner_id', $this->_sqlsnnipet_keys)) {
			$this->_sqlsnnipet_keys[] = 'owner_id';
			$this->_sqlsnnipet .= "owner_id='$owner_id',";
		}
	}
	public function set_user_id($user_id){
		$this->user_id = $user_id;
		if (!in_array('user_id', $this->_sqlsnnipet_keys)) {
			$this->_sqlsnnipet_keys[] = 'user_id';
			$this->_sqlsnnipet .= "user_id='$user_id',";
		}
	}
	public function set_created_at($created_at){
		$this->created_at = $created_at;
	}

	public function get_id(){
		return $this->id;
	}
	public function get_name(){
		return $this->name;
	}
	public function get_path(){
		return $this->path;
	}
	public function get_url(){
		return path_join(Config::media, $this->path);
	}
	public function get_real_path(){
		return path_join(ROOT, Config::media, $this->path);
	}
	public function get_size(){
		return $this->size;
	}
	public function get_owner_type(){
		return $this->owner_type;
	}
	public function get_owner_id(){
		return $this->owner_id;
	}
	public function get_user_id(){
		return $this->user_id;
	}
	public function get_created_at(){
		return $this->created_at;
	}

	public function upload($tmpfile){
		global $now;
		$save_path = ROOT.Config::media;
		$save_url = Config::media;
		if(!empty($tmpfile['error'])){
			$error_num = $tmpfile['error'];
			$error_arr = array(
				1 => '超过php.ini允许的大小。',
				2 => '超过表单允许的大小。',
				3 => '图片只有部分被上传。',
				4 => '请选择图片。',
				6 => '找不到临时目录。',
				7 => '写文件到硬盘出错。',
				8 => 'File upload stopped by extension。'
			);
			if(array_key_exists($error_num, $error_arr)){
				$this->alert($error_arr[$error_num]);
			}else{
				$this->alert("未知错误。");
			}
		}
		$file_name = $tmpfile['name'];
		$tmp_name = $tmpfile['tmp_name'];
		$file_size = $tmpfile['size'];
		if(!$file_name){$this->alert('请选择文件');}
		if(@is_dir($save_path)===false){$this->alert("上传目录不存在。");}
		if(@is_writable($save_path)===false){$this->alert("上传目录没有写权限。");}
		if(@is_uploaded_file($tmp_name)===false){$this->alert("上传失败。");}
		if($file_size > 100*1024*1024){$this->alert("上传文件大小超过限制。");}
		$file_ext = strtolower(trim(array_pop(explode(".", $file_name))));
		if ($file_ext == 'php') {$this->alert("禁止上传php文件");}
		$save_path .= $this->owner_type . "/";
		$save_url .= $this->owner_type . "/";
		if(!file_exists($save_path)){mkdir($save_path);}    //如果文件夹不存在这创建
		$ym = date("Ym", $now);
		$save_path .= $ym . "/";
		$save_url .= $ym . "/";
		if(!file_exists($save_path)){mkdir($save_path);}    //不在则创建
		$new_file_name = date("dHis", $now) . '_' . rand(100000000, 999999999) . '.' . $file_ext;
		$file_path = $save_path . $new_file_name;
		if(move_uploaded_file($tmp_name, $file_path) === false){
			$this->alert("上传文件失败。");
		}
		@chmod($file_path, 0644);
		$file_url = $save_url . $new_file_name;

		$this->set_name($file_name);
		$this->set_path(str_replace(Config::media, '', $file_url));
		$this->set_size($file_size);
		if(!$this->owner_id === null){
			$this->set_owner_id(0);
		}
		$this->save();
	}
	public function echo_after_upload($arg){
		header('Content-type: application/json; charset=UTF-8');
		$id = $this->get_id();
		$url = Config::media . $this->get_path();
		if ($arg == 'id') {
			echo json_encode(array('error' => 0, 'id' => $id));
		} elseif ($arg == 'url') {
			echo json_encode(array('error' => 0, 'url' => $url));
		}
		exit();
	}

	public function save(){
		global $now, $datetime;
		$this->_sqlsnnipet = ' ' . rtrim($this->_sqlsnnipet,',') . ' ';
		Db::query("insert into ".Config::table_pre."file set $this->_sqlsnnipet, created_at='$datetime'");
		$this->id = Db::insert_id();
	}

	public function update(){
		$this->_sqlsnnipet = ' ' . rtrim($this->_sqlsnnipet,',') . ' ';
		Db::query("update ".Config::table_pre."file set $this->_sqlsnnipet where id='$this->id'");
	}

	public function update_field($field, $value, $where_id=null){
		if(!$where_id){
			$where_id = $this->id;
		}
		Db::query("update ".Config::table_pre."file set `$field`='$value' where id='$where_id'");
	}

	public function delete(){
		if ($this->path !== null) {
			$file_path = path_join(ROOT, Config::media, $this->get_path());
			unlink($file_path);
		}
		Db::query("delete from ".Config::table_pre."file where id='$this->id'");
	}

	private function alert($msg){
		header('Content-type: text/html; charset=UTF-8');
		echo json_encode(array('error' => 1, 'message' => $msg));
		exit();
	}

}