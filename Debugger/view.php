<?php

Imports("MVC.view");

/**
 * 调试器的输出视图引擎
 * 必须要为web应用程序定义一个访问控制器，这个调试器才可以正常工作
*/
class debugView {
        
    /**
     * @var array
    */
    private static $Events;
    private static $vars;

    /**
     * @param string $event
    */
    public static function LogEvent($event) {
        self::$Events[] = [
            "time"        => time(), 
            "description" => $event
        ];
    }

    public static function DebugVars($vars) {
        if (empty($vars)) {
            self::$vars = [];
        } else {
            self::$vars = $vars;
        }        
    }

    /**
     * 获取调试器终端的视图模板文件的文件路径
    */
    private static function Template() {
        return dirname(__FILE__) . "/console.html";
    } 

    /**
     * 在这里主要是将变量组织之后传递给视图引擎进行调试器视图的渲染
    */
    public static function Display() {        
        View::Show(debugView::Template(), array_merge([
            "Includes" => self::Includes(),
            "Events"   => self::$Events,
            "Vars"     => self::Vars()
        ], self::Summary()), null, true);
    }

    private static function Vars() {
        $vars = [];

        foreach(self::$vars as $name => $value) {
            $vars[] = [
                "name"  => $name, 
                "value" => $value
            ];
        }

        return $vars;
    }

    private static function Summary() {
        return [
            "files"       => count(get_included_files()),
            "memory_size" => FileSystem::Lanudry(memory_get_usage()),
            "total_time"  => time() - $_SERVER["REQUEST_TIME"]
        ];
    }

    /**
     * ``[path => size]``
    */
	private static function Includes() {
        // 获取所加载的所有脚本列表
        $includes = [];
        
        foreach(get_included_files() as $file) {
            $includes[] = [
                "path" => $file, 
                "size" => FileSystem::Lanudry(filesize($file))
            ];
        }

        return $includes;
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