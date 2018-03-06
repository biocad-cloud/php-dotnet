<?php

class dotnetDebugger {

	public $mysql_history;
	
	function __construct() {
		$this->mysql_history = array();
	}
	
	public function add_mysql_history($SQL) {
		array_push($this->mysql_history, array($SQL, null));
	}

	public function add_last_mysql_error($error) {
		$lasti = count($this->mysql_history) - 1;
		$last  = $this->mysql_history[$lasti];
		$last  = array($last[0], $error);

		$this->mysql_history[$lasti] = $last; 
	}
}
?>