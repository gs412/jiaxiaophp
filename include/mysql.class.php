<?php

class Db
{
	private static $sql_log_start = false;
	private static $stmts = [];

	public static function query($sql, $array=array())
	{
		global $pdo;

		if ($array) {
			if (!isset(self::$stmts[$sql])) {
				if (Config::debug) {
					self::log("prepare: ".$sql);
				}
				$stmt = $pdo->prepare($sql);
				self::$stmts[$sql] = $stmt;
			}
			if (Config::debug) {
				self::log($sql." ->execute: ".print_r($array, true));
			}
			self::$stmts[$sql]->execute($array);
			$query = self::$stmts[$sql];
		} else {
			if (Config::debug) {
				self::log($sql);
			}
			$query = $pdo->query($sql);
		}
		return $query;
	}

	public static function find($sql, $array=array())
	{
		$rs = self::query($sql, $array);
		if($rs){
			$rs->setFetchMode(PDO::FETCH_OBJ);
			return $rs->fetch();
		}else{
			return null;
		}
	}

	public static function findall($sql, $array=array())
	{
		$rs = self::query($sql, $array);
		if($rs){
			$rs->setFetchMode(PDO::FETCH_OBJ);
			return $rs->fetchAll();
		}else{
			return [];
		}
	}

	public static function get($sql, $array=array())
	{
		$rs = self::query($sql, $array);
		if($rs){
			return $rs->fetchColumn();
		}else{
			return null;
		}
	}

	public static function getall($sql, $array=array())
	{
		$rs = self::query($sql, $array);
		if($rs){
			$rs->setFetchMode(PDO::FETCH_NUM);
			$array = $rs->fetchAll();
			$array = array_map(function($e){
				return $e[0];
			}, $array);
			return $array;
		}else{
			return [];
		}
	}

	public static function insert_id()
	{
		global $pdo;

		return $pdo->lastInsertId();
	}

	public static function table($table_name)
	{
		return new Table($table_name);
	}

	public static function close()
	{
		global $pdo;

		$pdo = null;
	}

	public static function log($str){
		$logfile = ROOT."sql_log.log";
		if(!self::$sql_log_start){
			self::$sql_log_start = true;
			$header = "\n\n=======================================================================================> ".date("Y-m-d H:i:s")."\n";
			$header .= "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\n";
			if(REQUEST_METHOD == 'POST'){
				$header .= "POST: ".json_encode($_POST)."\n";
			}
			$header .= "\n\n";
			file_put_contents($logfile, $header, FILE_APPEND);
		}
		if (strlen($str) > 1000) {
			$str = substr($str, 0, 1000) . '......';
		}
		file_put_contents($logfile, "$str\n", FILE_APPEND);
	}

}


class Table
{
	private $table_name = '';

	public function __construct($table_name)
	{
		$this->table_name = Config::table_pre . $table_name;
		return $this;
	}

	public function create($data)
	{
		global $now, $datetime;

		$fields = $this->get_db_fields();
		$fields = array_column_php7($fields, 'type', 'name');

		foreach ($data as $key=>&$val) {
			if (!isset($fields[$key])) {
				unset($data[$key]);
			}
			if ($fields[$key]=='datetime' and is_numeric($val)) {
				$val = date("Y-m-d H:i:s", $val);
			} elseif ($fields[$key]=='int' and preg_match("/^\d{2,4}-\d{1,2}-\d{1,2}/is", $val)) {
				$val = strtotime($val);
			} elseif (in_array($fields[$key], ['varchar','text']) and !$val and $val!=0) {
				$val = '';
			}
		}

		if (isset($fields['created_at']) and !isset($data['created_at'])) {
			if ($fields['created_at'] == 'datetime') {
				$data['created_at'] = $datetime;
			} elseif ($fields['created_at'] == 'int') {
				$data['created_at'] = $now;
			}
		}

		$data_keys = array_keys($data);
		$set_strs = array_map(function ($key) {
			return "`$key`=?";
		}, $data_keys);
		$set_strs = implode(',', $set_strs);

		Db::query("INSERT INTO {$this->table_name} SET {$set_strs}", array_values($data));
	}

	public function update($data)
	{
		$fields = $this->get_db_fields();
		$fields = array_column_php7($fields, 'type', 'name');

		foreach ($data as $key=>&$val) {
			if (!isset($fields[$key])) {
				unset($data[$key]);
			}
			if ($fields[$key]=='datetime' and is_numeric($val)) {
				$val = date("Y-m-d H:i:s", $val);
			} elseif ($fields[$key]=='int' and preg_match("/^\d{2,4}-\d{1,2}-\d{1,2}/is", $val)) {
				$val = strtotime($val);
			} elseif (in_array($fields[$key], ['varchar','text']) and !$val and $val!=0) {
				$val = '';
			}
		}
		$id = $data['id'];
		unset($data['id']);

		$data_keys = array_keys($data);
		$set_strs = array_map(function ($key) {
			return "`$key`=?";
		}, $data_keys);
		$set_strs = implode(',', $set_strs);

		$args = array_values($data);
		$args[] = $id;

		Db::query("UPDATE {$this->table_name} SET {$set_strs} WHERE id=?", $args);
	}

	public function find_by_id($id)
	{
		return Db::find("SELECT * FROM {$this->table_name} WHERE id=?", [$id]);
	}

	public function delete_by_id($id)
	{
		return Db::query("DELETE FROM {$this->table_name} WHERE id=?", [$id]);
	}

	public function get_db_fields()
	{
		$fields = Db::findall("SELECT COLUMN_NAME as name, DATA_TYPE as type FROM information_schema.COLUMNS WHERE table_name=? AND table_schema=?", [$this->table_name, Config::dbname]);
		return $fields;
	}
}