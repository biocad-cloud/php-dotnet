<?php

class debugView {
	
	public static function GetView($engine) {
        // 获取所加载的所有脚本列表
        $includes = get_included_files();
	}
    
    /**
     * 将当前的会话之中所使用到的MySQL查询导出来 
     * 
     * @param engine: 
    */
	public static function GetMySQLView($engine) {
		$template = '<li class="dotnet-mysql-debugger">%s</li>';
		$html     = "";
		
		foreach ($engine->mysql_history as $sql) {
            $error  = $sql[1];
            $sql    = $sql[0];

            if (!$error) {
                $li = sprintf($template, $sql) . "\n";
            } else {
                $li = $sql . "\n\n<code><pre>" . $error . "</pre></code>";
                $li = sprintf($template, $li) . "\n";
            }
			
			$html  = $html . $li;
		}
		
		return $html;
	}
}
?>