<?php

class dotnetDebugger {

	public $mysql_history;
	
	function __construct() {
		$this->mysql_history = array();
	}
	
	public function add_mysql_history($SQL) {
		array_push($this->mysql_history, $SQL);
	}
}
?>