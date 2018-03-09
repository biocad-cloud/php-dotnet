<?php

class dotnetDebugger {

	public $mysql_history;
	
	function __construct() {
		$this->mysql_history = array();
	}
	
	public function hasMySqlLogs() {
		return count($this->mysql_history) > 0;
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

		$this->mysql_history[$lasti] = $last; 
	}
}
?>