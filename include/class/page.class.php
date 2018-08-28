<?php


class Page {

	private $table = '';
	private $perpage = 20;
	private $where = '';
	private $order_by = '';
	private $sql = '';
	private $total = null;

	private $page;     //下面这2个作内部计算用
	private $objects;
	private $pagestr;
	private $pagestr_mobi;
	private $page_real_num;
	private $has_run = false;


	function __construct($table) {
		$this->table = Config::table_pre.$table;
	}

	public function set_perpage($perpage) {
		$this->perpage = $perpage;
	}

	public function set_where($where) {
		$this->where = $where;
	}

	public function set_order_by($order_by) {
		$this->order_by = $order_by;
	}

	public function set_sql($sql) {
		$this->sql = $sql;
	}

	public function set_total($total) {     // 设置记录总数，可不设，不设的话就在下面的run方法中通过select count(*)查询数据库统计
		$this->total = $total;
	}


	private function run() {
		if ($this->where) {
			$where = " where {$this->where} ";
		} else {
			$where = '';
		}
		if ($this->order_by) {
			$order_by = " order by {$this->order_by} ";
		} else {
			$order_by = '';
		}

		if ($this->total == null) {
			$this->total = Db::get("select count(*) from {$this->table} {$where}");
		}
		$this->page = $this->get_page_num($this->total, $this->perpage);
		$this->pagestr = $this->get_page_str($this->total, $this->perpage, $this->page);
		$this->pagestr_mobi = $this->get_page_str_mobi($this->total, $this->perpage, $this->page);

		if (!$this->sql) {
			$sql = $this->fenye_youhua_sql("select * from {$this->table} {$where} {$order_by} limit ".(($this->page-1)*$this->perpage).",{$this->perpage}", $this->table);
		} else {
			$sql = $this->fenye_youhua_sql("{$this->sql} limit ".(($this->page-1)*$this->perpage).",{$this->perpage}", $this->table);
		}
		$this->objects = Db::findall($sql);
		$this->has_run = true;
	}

	// 分页优化
	private function fenye_youhua_sql($sql, $table)
	{
		preg_match("/limit\s*(\d+)\s*,\s*(\d+)/is", $sql, $out);
		$start = $out[1];
		$perpage = $out[2];

		$order_by_pattern = "/order\s+by\s+(.*)\s+limit/is";
		if (preg_match($order_by_pattern, $sql, $out)) {
			$order_by = $out[1];
		} else {
			$order_by = 'id asc';
		}
		$first_order_by = explode(',',$order_by)[0];
		list($limit_key, $gt_or_lt) = preg_split("/\s+/is", trim($first_order_by));
		$gt_or_lt = ['asc'=>'>=', 'desc'=>'<='][strtolower($gt_or_lt)];

		$sql = preg_replace("/limit .*/is", "", $sql);
		$sql = preg_replace("/order\s+by .*/is", "", $sql);

		if (preg_match("/\s(left|right|inner)\s+join\s/is", $sql)) {
			list($tn, $limit_key1) = explode('.', $limit_key);
			if (preg_match("/(\w+)\s+as\s+$tn/is", $sql, $out)) {
				$table1 = $out[1];
			} else {
				$table1 = $tn;
			}
			list($tn, $first_order_by1) = explode('.', $first_order_by);
			$inner_sql = "select {$limit_key1} from {$table1} order by {$first_order_by1} limit {$start}, 1";
		} else {
			$inner_sql = "select {$limit_key} from {$table} order by {$first_order_by} limit {$start}, 1";
		}

		$group_by_split = preg_split("/group\s+by/is", $sql);
		if (count($group_by_split) == 2) {
			$sql = $group_by_split[0];
			$group_by = ' group by ' . $group_by_split[1];
		} else {
			$group_by = '';
		}

		$sql = $sql . (preg_match("/ where /is", $sql)?' and ':' where ') . " {$limit_key} $gt_or_lt ($inner_sql) $group_by order by {$order_by} limit {$this->perpage}";
		return $sql;
	}

	public function get_objects() {
		if (!$this->has_run) {
			$this->run();
		}
		return $this->objects;
	}

	public function get_pagestr() {
		if (!$this->has_run) {
			$this->run();
		}
		return $this->pagestr;
	}

	public function get_pagestr_mobi() {
		if (!$this->has_run) {
			$this->run();
		}
		return $this->pagestr_mobi;
	}

	public function get_total_num()
	{
		return $this->total;
	}

	public function get_page_count()
	{
		return ceil($this->total/$this->perpage);
	}

	public function get_page_real_num()
	{
		return $this->page_real_num;
	}


	private function get_page_num($total, $perpage)
	{
		$page = $_GET['page'];
		if(!preg_match("/^\\d+$/", $page)){
			$page = 1;
		}
		$this->page_real_num = max(min(ceil($total/$perpage),$page),1);
		return $this->page_real_num;
	}

	private function get_page_str($total, $perpage, $page)
	{
		$url = $_SERVER['REQUEST_URI'];
		if(has_str($url, "page=")){
			$url = preg_replace("/page=\\d*/is", 'page=<page_num>', $url);
		}else{
			if(has_str($url, '.php?') or has_str($url, '?')){
				$url = $url . "&page=<page_num>";
			}else{
				$url = $url . "?page=<page_num>";
			}
		}

		$page_count = ceil($total/$perpage);
		$array = array();
		if($page < 9 or $total < 12){
			$array = array_merge($array, range(1, $page));
		}else{
			$array = array_merge($array, range(1, 2));
			array_push($array, "dot");
			$array = array_merge($array, range(min($page-4, $page_count-8),$page));
		}
		if($page > $page_count-8 or $total < 12){
			if($page < $page_count){
				$array = array_merge($array, range($page+1 ,$page_count));
			}
		}else{
			$array = array_merge($array, range($page+1,max($page+4, 9)));
			array_push($array, "dot");
			$array = array_merge($array, range($page_count-1,$page_count));
		}

		$array = array_map(function($i)use($url, $page){
			if($i == 'dot'){
				return '<span class="dot">.....</span>';
			}elseif($i == $page){
				return "<span class='current'>$i</span>";
			}else{
				return  "<a href='".str_replace("<page_num>", $i, $url)."'>$i</a>";
			}
		}, $array);
		return join('', $array);
	}

	private function get_page_str_mobi($total, $perpage, $page)
	{
		$url = $_SERVER['REQUEST_URI'];
		if(has_str($url, "page=")){
			$url = preg_replace("/page=\\d*/is", 'page=<page_num>', $url);
		}else{
			if(has_str($url, '.php?') or has_str($url, '?')){
				$url = $url . "&page=<page_num>";
			}else{
				$url = $url . "?page=<page_num>";
			}
		}

		$page_count = ceil($total/$perpage);
		$prev_num = $page - 1;
		$next_num = $page + 1;
		$result = '';
		if ($page > 1) {
			$result .= "<span class='prev_page'><a href='".str_replace("<page_num>", $prev_num, $url)."'>上一页</a></span>";
		} else {
			$result .= "<span class='prev_page'><a href='###'>　　　</a></span>";
		}
		$result .= "<span class='page_status'>$page/$page_count</span>";
		if ($page < $page_count) {
			$result .= "<span class='next_page'><a href='".str_replace("<page_num>", $next_num, $url)."'>下一页</a></span>";
		} else {
			$result .= "<span class='next_page'><a href='###'>　　　</a></span>";
		}
		return $result;
	}


}