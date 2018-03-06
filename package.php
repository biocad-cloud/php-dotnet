<?php

include_once dotnet::GetDotnetManagerDirectory() . "/System/Diagnostics/StackTrace.php";
include_once dotnet::GetDotnetManagerDirectory() . "/Debugger/engine.php";
include_once dotnet::GetDotnetManagerDirectory() . "/Debugger/view.php";
include_once dotnet::GetDotnetManagerDirectory() . "/Microsoft/VisualBasic/Strings.php";
include_once dotnet::GetDotnetManagerDirectory() . "/Microsoft/VisualBasic/ApplicationServices/Debugger/Logging/LogFile.php";
include_once dotnet::GetDotnetManagerDirectory() . "/RFC7231/index.php";
include_once dotnet::GetDotnetManagerDirectory() . "/Registry.php";

session_start();

function test233($str) {
    echo "hello!  $str";
}

/**
 * dotnet package manager, you must include this module at first.
 * 
 * 在php之中有一个DOTNET类型：http://php.net/manual/en/class.dotnet.php
 * 但是这个模块的使用有诸多的限制，假若使用本项目的时候，发现出现错误:
 * 
 * Fatal error: Cannot redeclare class dotnet in "mod\php.NET\package.php" on line 6
 * 
 * 则应该要检查一下你的php服务器的设置是否是区分大小写的？
 * 因为这个类名称dotnet假若不区分大小写的话，是和系统自带的DOTNET类型同名的
 *
 * php 不像VB.NET一样允许函数重载，所以同一个class模块之中不可以出现相同名字的函数
 *
 */
class dotnet {

    /**
     * 只需要修改这个参数的逻辑值就可以打开或者关闭调试器的输出行为
     */
    public static $debug = True;
    public static $error_log;
    public static $debugger;

    /**
     * This method have not implemented yet!
     * 
     * Usage:
     *      die(dotnet::$MethodNotImplemented);
     */
    const MethodNotImplemented = "This method have not implemented yet!";
    
    /**
     * 获取得到package.php这个文件的文件路径
     * 
     * @return string
     */
    const DotNetManagerFileLocation = __FILE__;

	public static function AutoLoad($config) {		
        
        date_default_timezone_set('UTC');

		dotnet::Imports("MVC.view");
		dotnet::Imports("MVC.model");
		dotnet::Imports("MVC.control");
        dotnet::Imports("MVC.driver");       
        
        DotNetRegistry::$config = include $config;
        
        if (!DotNetRegistry::DisableErrorHandler()) {
            self::setupLogs();
        }
    }
    
    private static function setupLogs() {
        // 使用本框架的错误处理工具
        dotnet::$error_log = new LogFile(DotNetRegistry::LogFile());                    

        # echo dotnet::$logs->handle . "\n";

        // Report all PHP errors (see changelog)
        error_reporting(E_ALL);
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
             # 2018-3-5 Call to a member function LoggingHandler() on a non-object
             $logs = dotnet::$error_log;
             $logs->LoggingHandler($errno, $errstr, $errfile, $errline);
        }, E_ALL);
    }
    
    public static function printMySqlTransaction() {

    }

    /**
     * 对于这个函数额调用者而言，就是获取调用者所在的脚本的文件夹位置
     * 这个函数是使用require_once来进行模块调用的
     *
     * @param mod: 直接为命名空间的路径，不需要考虑相对路径或者添加文件后缀名，例如需要导入VisualBasic的Strings模块的方法，只需要调用代码
     * 
     *     dotnet::Imports("Microsoft.VisualBasic.Strings");
     * 
     * @return string 这个函数返回所导入的模块的完整的文件路径
     * 
     */
    public static function Imports($mod) {  	
        
        $DIR = self::GetDotnetManagerDirectory();
        
		// echo $DIR;
		
        // 因为WithSuffixExtension这个函数会需要依赖小数点来判断文件拓展名，
        // 所以对小数点的替换操作要在if判断之后进行  
        if (dotnet::WithSuffixExtension($mod, "php")) {
            $mod = str_replace(".", "/", $mod); 
            $mod = "{$DIR}/{$mod}";
        } else {
            $mod = str_replace(".", "/", $mod); 
            $mod = "{$DIR}/{$mod}.php";
        }   
                
        // echo $mod."<br/>";

        // 在这里导入需要导入的模块文件
        include_once($mod);
        // 返回所导入的文件的全路径名
        return $mod;
    }

    /**
     * 判断这个文件路径是否是以特定的文件拓展名结尾的？这个函数大小写不敏感
     */
    public static function WithSuffixExtension($path, $ext) {
        $array  = Strings::Split($path, "\.");
        $lastEl = array_values(array_slice($array, -1));
        $lastEl = $lastEl[0];

        // echo $lastEl . "<br />";
        // echo $ext;

        return Strings::LCase($lastEl) == Strings::LCase($ext);
    }

    /**
     * 获取得到package.php这个文件的所处的文件夹的位置
     */
    public static function GetDotnetManagerDirectory() {
        return dirname(self::DotNetManagerFileLocation);
    }

	/*
	 * PHP throw exception helper
	 */
    public static function ThrowException($message) {      
		$trace = StackTrace::GetCallStack();
		$exc   = dotnetException::FormatOutput($message, $trace);
				
		Error::err500($exc);
		exit(0);
    }
}

class dotnetException extends Exception {
	
	public $stackTrace;
	public $message;
	
	function __constructor($message) {
		$this->message    = $message;
		$this->stackTrace = StackTrace::GetCallStack();
	}	
	
	public static function FormatOutput($message, $stackTrace) {
		$str = "<div class='dotnet-exception'>";
		$str = $str . "<p><span style='color:red'>" . $message . "</span>" ;
		$str = $str . "\n";
		$str = $str . "<p>";
		$str = $str . $stackTrace;
		$str = $str . "</p></p>";
		$str = $str . "</div>";
		
		return $str;
	}
	
	public static function FormatExceptionOutput($ex) {
		return self::FormatOutput($ex->message, $ex->stackTrace);
	}
	
	public function __toString() {
		return self::FormatExceptionOutput(
			$this->message, 
			$this->stackTrace
		);
	}
}

?>