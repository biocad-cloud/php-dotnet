<?php

include_once dotnet::GetDotnetManagerDirectory() . "/php/Utils.php";

include_once dotnet::GetDotnetManagerDirectory() . "/System/Diagnostics/StackTrace.php";
include_once dotnet::GetDotnetManagerDirectory() . "/System/Text/StringBuilder.php";
include_once dotnet::GetDotnetManagerDirectory() . "/Debugger/dotnetException.php";
include_once dotnet::GetDotnetManagerDirectory() . "/Debugger/engine.php";
include_once dotnet::GetDotnetManagerDirectory() . "/Debugger/view.php";
include_once dotnet::GetDotnetManagerDirectory() . "/Microsoft/VisualBasic/Strings.php";
include_once dotnet::GetDotnetManagerDirectory() . "/Microsoft/VisualBasic/ApplicationServices/Debugger/Logging/LogFile.php";
include_once dotnet::GetDotnetManagerDirectory() . "/RFC7231/index.php";
include_once dotnet::GetDotnetManagerDirectory() . "/Registry.php";

session_start();
# PHP Warning:  date(): It is not safe to rely on the system's timezone settings. 
# You are *required* to use the date.timezone setting or the date_default_timezone_set() function. 
# In case you used any of those methods and you are still getting this warning, you most likely 
# misspelled the timezone identifier. We selected the timezone 'UTC' for now, 
# but please set date.timezone to select your timezone.
date_default_timezone_set('UTC');

$APP_DEBUG = true;

/**
 * Global function for load php.NET package 
 * 
 */
function Imports($namespace) {
    return dotnet::Imports($namespace, $initiatorOffset = 1);
}

/**
 * 对用户的浏览器进行重定向
 * 
*/
function Redirect($URL) {   
    header("Location: " . View::AssignController($URL));
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

    public static $error_log;
    public static $debugger;
    public static $AppDebug;

    // 函数返回成功消息的json字符串
    public static function successMsg($msg) {	
		return json_encode(array(
			'code' => 0,
            'info' => $msg)
        );
	}
    
    // 函数返回失败消息的json字符串
	public static function errorMsg($msg, $errorCode = 1) {
		return json_encode(array(
			'code' => $errorCode,
            'info' => $msg)
        );
	}

    public static function HandleRequest($app, $wwwroot = NULL) {
        if ($wwwroot) {
            DotNetRegistry::SetMVCViewDocumentRoot($wwwroot);
        }

        Router::HandleRequest($app);
    }

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

    /**
     * 进行php.NET核心模块的加载操作 
     * 
     * @param config: php.NET框架核心正常工作所需要的配置参数，如果忽略掉这个参数的话将会使用默认配置
     *                在默认配置下，mysql数据库模块将会无法正常工作，因为没有mysql的链接参数信息。
     *                这个参数可以有两种形式：
     *                1. php文件路径，如果文件不存在，则会使用默认配置数据
     *                2. 包含有配置数据的字典数组
     * 
     * @param debug: 只需要修改这个参数的逻辑值就可以打开或者关闭调试器的输出行为
     *
     **/
	public static function AutoLoad($config = NULL, $debug = FALSE) {		       

        define('APP_PATH',  dirname(__FILE__)."/");
        define("APP_DEBUG", $debug);

        self::$AppDebug = $debug;

        if (self::$AppDebug) {
            # 调试器必须先于Imports函数调用，否则会出现错误：
            # PHP Fatal error:  Call to a member function add_loaded_script() on a non-object
            dotnet::$debugger = new dotnetDebugger();    
        }   

		dotnet::Imports("MVC.view");
		dotnet::Imports("MVC.model");
		dotnet::Imports("MVC.router");
        dotnet::Imports("MVC.driver");       
        
        if ($config) {
            # config存在赋值，则判断一下是否为字符串？
            # 如果是字符串，则使用文件的加载方式
            # 反之再判断是否为数组
            # 如果既不是字符串又不是数组，则使用默认配置数据并给出警告
            if (is_string($config) && file_exists($config)) {
                DotNetRegistry::$config = include $config;
            } elseif (is_array($config)) {
                DotNetRegistry::$config = $config;
            } else {
                # 无效的配置参数信息，使用默认的配置并且给出警告信息
                DotNetRegistry::$config = DotNetRegistry::DefaultConfig();
            }
        } else {
            DotNetRegistry::$config = DotNetRegistry::DefaultConfig();
        }

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
        if (self::$AppDebug) {
            echo debugView::GetMySQLView(self::$debugger);
        }
    }

    /**
     * php写日志文件只能够写在自己的wwwroot文件夹之中 
     **/
    public static function writeMySqlLogs($onlyErrors = FALSE) {      
        if (self::$AppDebug) {
            if (self::$debugger->hasMySqlLogs()) {
                if ($onlyErrors) {
                    if (self::$debugger->hasMySqlErrs()) {
                        self::writeMySqlLogs2();
                    }
                } else {
                    self::writeMySqlLogs2();
                }            
            }
        }
    }

    private static function writeMySqlLogs2() {
        $log = "./data/mysql_logs.html";

        FileSystem::WriteAllText($log, "<h5>API: $_SERVER[REQUEST_URI]</h5>\n", TRUE);
        FileSystem::WriteAllText($log, "<ul>" . debugView::GetMySQLView(self::$debugger) . "</ul>\n", TRUE);
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
    public static function Imports($mod, $initiatorOffset = 0) {  	
                
        $DIR = self::GetDotnetManagerDirectory();
        	
        // 因为WithSuffixExtension这个函数会需要依赖小数点来判断文件拓展名，
        // 所以对小数点的替换操作要在if判断之后进行  
        if (Utils::WithSuffixExtension($mod, "php")) {
            $mod = str_replace(".", "/", $mod); 
            $mod = "{$DIR}/{$mod}";
        } else {
            $mod = str_replace(".", "/", $mod); 
            $mod = "{$DIR}/{$mod}.php";
        }   

        // 在这里导入需要导入的模块文件
        include_once($mod);
        
        if (self::$AppDebug) {
            $bt    = debug_backtrace();             
            $trace = array();

            foreach($bt as $k=>$v) { 
                // 解析出当前的栈片段信息
                extract($v); 
                array_push($trace, $file);    
            } 

            $initiatorOffset = 1 + $initiatorOffset;
            $initiator       = $trace[$initiatorOffset];

            # echo var_dump(self::$debugger);

            self::$debugger->add_loaded_script($mod, $initiator);
        }

        // 返回所导入的文件的全路径名
        return $mod;
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
				
		RFC7231Error::err500($exc);
		exit(0);
    }
}
?>