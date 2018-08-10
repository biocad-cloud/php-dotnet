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
    private static $debugger;

    /**
     * 添加App的事件记录
     * 
     * @param string $event
    */
    public static function LogEvent($event) {
        self::$Events[] = [
            "time"        => Utils::Now(false), 
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
        return dirname(__FILE__) . "/template/console.html";
    }

    /**
     * 在这里主要是将变量组织之后传递给视图引擎进行调试器视图的渲染
    */
    public static function Display() {        
        # 在这里自动添加结束标记
        self::LogEvent("--- App Exit ---");
        
        View::Show(
            debugView::Template(), 
            debugView::union(), 
            null, true
        );
    }

    private static function union() {
        return array_merge([
            "Includes" => self::Includes(),
            "Events"   => self::$Events,
            "MySql"    => self::GetMySQLView(dotnet::$debugger),
            "Console"  => console::$logs,
            "Vars"     => self::Vars()
        ], self::Summary());
    }

    private static function Vars() {
        $vars = [];

        foreach(self::$vars as $name => $value) {           
            $vars[] = [
                "name"  => $name, 
                "value" => console::objDump($value, false)
            ];
        }

        return $vars;
    }

    private static $summary;    

    public static function AddItem($name, $value) {
        if (!self::$summary) {
            self::$summary = [];
        }
        
        self::$summary[$name] = $value;
    }

    private static function Summary() {
        $js   = dirname(self::Template()) . "/jquery.jsonview.min.js";        
        $js   = base64_encode(file_get_contents($js));
        $css  = dirname(self::Template()) . "/jquery.jsonview.min.css";
        $css  = file_get_contents($css);        
        $vars = array_merge([
            "files"           => count(get_included_files()),
            "memory_size"     => FileSystem::Lanudry(memory_get_usage()),            
            "json_viewer_js"  => $js,
            "json_viewer_css" => $css
        ], self::$summary);

        return self::benchmark($vars);
    }

    private static function benchmark($vars) {
        # Benchmark : {$benchmark} ( Load:{$benchmark.load} Init:{$benchmark.init} Exec:{$benchmark.exec} Template:{$benchmark.template}
        $total = $vars["benchmark.load"] + $vars["benchmark.init"] + $vars["benchmark.exec"] + $vars["benchmark.template"];
        $n     = 1000 / $total;
        $total = Ubench::readableElapsedTime($total);

        $vars["benchmark"]   = $total;
        $vars["total_time"]  = $total;
        $vars["benchmark_n"] = round($n, 3);

        $vars["benchmark.load"]     = Ubench::readableElapsedTime($vars["benchmark.load"]);
        $vars["benchmark.init"]     = Ubench::readableElapsedTime($vars["benchmark.init"]);
        $vars["benchmark.exec"]     = Ubench::readableElapsedTime($vars["benchmark.exec"]);
        $vars["benchmark.template"] = Ubench::readableElapsedTime($vars["benchmark.template"]);

        return $vars;
    }

    /**
     * ``[path => size]``
    */
	private static function Includes() {
        // 获取所加载的所有脚本列表
        $includes = [];
        
        foreach(get_included_files() as $file) {
            if (Strings::InStr($file, "data://text") == 1) {
                $size = strlen($file);
                $file = "<code>" . substr($file, 0, 50) . "...</code>";                
            } else {
                $size = filesize($file);
            }

            $includes[] = [
                "path" => $file, 
                "size" => FileSystem::Lanudry($size)
            ];
        }

        return $includes;
	}
    
    /**
     * 将当前的会话之中所使用到的MySQL查询导出来 
     * 
     * @param dotnetDebugger $engine 
    */
	public static function GetMySQLView($engine) {
		
        $mysql_history = $engine->mysql_history;

        $MySql = array();
        
        $queries = 0;
        $writes  = 0;

		foreach ($mysql_history as $sql) {
            
            $error = $sql["err"];
            $time  = $sql["time"];
            $type  = $sql["type"];
            $sql   = $sql["sql"];
            
            if($type == "queries"){
                $queries++;
            }else{
                $writes++;
            }
        
            //如果error不存在说明这条语句执行正常 
            if (!$error) {
                $error = '';  
            }
            
            $info = array(
                "error" => $error,
                "sql"   => $sql,
                "time"  => $time
            );
            
            array_push($MySql, $info);
        }

        self::AddItem("sql.queries", $queries);
        self::AddItem("sql.writes", $writes);

		return $MySql;
	}
}
?>