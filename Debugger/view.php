<?php

class debugView {
	
	public static function GetView($engine) {
		
	}
	
	public static function GetMySQLView($engine) {
		$template = '<li class="dotnet-mysql-debugger">%s</li>';
		$html     = "";
		
		foreach ($engine->mysql_history as $sql) {
			$li   = sprintf($template, $sql) . "\n";
			$html = $html . $li;
		}
		
		return $html;
	}
}
?>