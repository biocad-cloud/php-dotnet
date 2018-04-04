<?php

class dotnetDebugger {

	public $mysql_history;
	private $setError;

	function __construct() {
		$this->mysql_history = array();
		$this->setError      = FALSE;
	}
	
	public function hasMySqlLogs() {
		return count($this->mysql_history) > 0;
	}

	public function hasMySqlErrs() {
		return $this->setError;
	}

	public function add_mysql_history($SQL) {
		array_push($this->mysql_history, array($SQL, null));
	}

	/**
	 * 如果上一条mysql执行出错了，则可以通过这个函数来将mysql的错误记录下来 
	 */
	public function add_last_mysql_error($error) {
		$lasti = count($this->mysql_history) - 1;
		$last  = $this->mysql_history[$lasti];
		$last  = array($last[0], $error);

		$this->setError              = TRUE;
		$this->mysql_history[$lasti] = $last; 
	}

	public static function GetLoadedFiles() {
		return get_included_files();
	}
}
?>