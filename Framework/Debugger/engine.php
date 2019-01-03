<?php

class dotnetDebugger {

	public $script_loading;
	public $mysql_history;

	// Mysql query in current session has errors?
	private $setError;

	function __construct() {
		$this->mysql_history  = array();
		$this->script_loading = array();
		$this->setError       = FALSE;
	}
	
	public function hasMySqlLogs() {
		return count($this->mysql_history) > 0;
	}

	public function hasMySqlErrs() {
		return $this->setError;
	}

	/**
	 * @param string $SQL
	 * @param string $type + ``writes``表示对数据库进行了sql操作，对数据库的数据有影响， 
	 * 					   + ``queries``表示只是对数据库进行了查询，对数据库的数据没有影响
	*/
	public function add_mysql_history($SQL, $elapsed, $type) {
		$this->mysql_history[] = [
			"sql"  => $SQL, 
			"time" => $elapsed,
			"type" => $type
		];
	}

	public function add_loaded_script($path, $refer) {
		$info = array(
			"module"    => $path, 
			"size"      => filesize($path), 
			"initiator" => $refer,
			"time"      => Utils::Now()
		);

		array_push($this->script_loading, $info);
	}

	/**
	 * @return array [sql => error]
	*/
	public function last_mysql_error() {
		return $this->mysql_history[count($this->mysql_history) - 1];
	}

	/**
	 * 如果上一条mysql执行出错了，则可以通过这个函数来将mysql的错误记录下来 
	*/
	public function add_last_mysql_error($error) {
		$lasti = count($this->mysql_history) - 1;
		$last  = $this->mysql_history[$lasti];
		$last["err"] = $error;

		$this->setError              = TRUE;
		$this->mysql_history[$lasti] = $last; 
	}

	public static function GetLoadedFiles() {
		return get_included_files();
	}

	/** 
	 * 判断当前的调用是否为调试器api调用，假设api调试器调用仅发生在index.php
	 * 
	 * ```
	 * index.php?api=debugger
	 * ```
	 * 
	 * 并且为POST提交方式进行请求
	*/
	public static function IsDebuggerApiCalls() {
		$calls = URL::mb_parse_url(null, true, true);

		if (!IS_POST) {
			return false;
		}

		if (basename($calls->path) != "index.php") {
			return false;
		}

		if (Utils::ReadValue($calls->query, "api") != "debugger") {
			return false;
		}

		return true;
	}

	public static function handleApiCalls() {

	}
}