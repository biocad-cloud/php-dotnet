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
        $template = debugView::Template();  
        $vars     = debugView::union();

        if (!array_key_exists(DEBUG_SESSION, $_SESSION)) {
            $_SESSION[DEBUG_SESSION] = [];
        }

        # 写入当前页面的调试器guid
        # 每一个html页面都是一个新的调试器会话，所以在这里总是对session写入新的数组的
        $guid = DEBUG_GUID;
        $_SESSION[DEBUG_SESSION][$guid] = [
            "url"  => Utils::URL(),
            "logs" => []
        ];
        $vars["debugger_guid"] = $guid;

        View::Show($template, $vars, null, true);
    }

    private static function union() {
        return array_merge([
            "Includes" => self::Includes(),
            "Events"   => self::$Events,
            "MySql"    => self::GetMySQLView(dotnet::$debugger),
            "Console"  => console::$logs,
            "Vars"     => self::Vars(),
            "Envir"    => self::Environment()
        ], self::Summary());
    }

    /**
     * ``$_SERVER`` 环境变量
    */
    private static function Environment() {
        $envir = [];
        
        foreach(Utils::ArrayCopy($_SERVER) as $name => $value) {
            if ($name === "PATH" || $name === "PATHEXT") {
                if (Utils::IsWindowsOS()) {
                    $value = explode(";", $value);
                } else {
                    $value = explode(":", $value);
                }                
            }
            $envir[] = [
                "name"  => $name, 
                "value" => console::objDump($value, false)
            ];
        }

        return $envir;
    }

    /**
     * 显示用户视图的页面参数
    */
    private static function Vars() {
        global $_DOC;

        $vars = [];

		if (!empty($_DOC)) {  
            $vars[] = [
                "name"  => "PHP_DOC", 
                "value" => console::objDump($_DOC, false)
            ];
        }

        if (Utils::IsSessionStart()) {
            $vars[] = [
                "name"  => "PHP_SESSION", 
                "value" => console::objDump($_SESSION, false)
            ];
        }

        $vars[] = [
            "name"  => "REQUEST", 
            "value" => console::objDump($_REQUEST, false)
        ];        

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

    /**
     * json组件需要jquery才能够正常工作
     * 
     * 因为调试环境下，服务器压力不是很大，所以在这里
     * 从文件读取之后进行模板的序列化填充不会产生很大的性能问题
    */
    private static function Summary() {
        $js   = dirname(self::Template()) . "/jquery.jsonview.min.js";        
        $js   = base64_encode(file_get_contents($js));
        $uiJs = dirname(self::Template()) . "/js_worker.js";
        $uiJs = base64_encode(file_get_contents($uiJs));
        $css  = dirname(self::Template()) . "/jquery.jsonview.min.css";
        $css  = file_get_contents($css);        
        $vars = array_merge([
            "files"           => count(get_included_files()),
            "memory_size"     => FileSystem::Lanudry(memory_get_usage()),
            "json_viewer_js"  => $js,
            "json_viewer_css" => $css,
            "git"             => GIT_COMMIT,
            "tabUI_js"        => $uiJs
        ], self::$summary);

        $vars["http.time"]     = \Utils::Now();
        $vars["http.protocol"] = $_SERVER["SERVER_PROTOCOL"];
        $vars["http.method"]   = $_SERVER["REQUEST_METHOD"];
        $vars["http.uri"]      = $_SERVER["REQUEST_URI"];
        $vars["http.php_session"] = session_id();

        return self::benchmark($vars);
    }

    /**
     * 性能计数器 
    */
    private static function benchmark($vars) {
        # Benchmark : {$benchmark} ( Load:{$benchmark.load} Init:{$benchmark.init} Exec:{$benchmark.exec} Template:{$benchmark.template}
        $total = $vars["benchmark.load"] + 
                 $vars["benchmark.init"] + 
                 $vars["benchmark.exec"] + 
                 $vars["benchmark.template"];
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
        $MySql   = [];        
        $queries = 0;
        $writes  = 0;

		foreach ($engine->mysql_history as $sql) {
            $error = Utils::ReadValue($sql, "err", "");
            $time  = $sql["time"];
            $type  = $sql["type"];
            $sql   = $sql["sql"];
            
            if ($type == "queries") {
                $queries++;
            } else {
                $writes++;
            }
        
            // 如果error不存在说明这条语句执行正常 
            if (!$error) {
                $error = '';  
            }
            
            $MySql[] = [
                "error" => $error,
                "sql"   => $sql,
                "time"  => $time
            ];
        }

        self::AddItem("sql.queries", $queries);
        self::AddItem("sql.writes", $writes);

		return $MySql;
	}
}
?>