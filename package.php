<?php

// APP_DEBUG常数在引用这个文件之前必须首先进行定义
if (!defined('APP_DEBUG')) {
    /**
     * 这个常数会影响框架的调试器的输出行为，默认是关闭调试器
    */
    define("APP_DEBUG", false);
}

/**
 * PHP.NET框架的根文件夹位置
 * 获取得到package.php这个文件的所处的文件夹的位置
*/
define("PHP_DOTNET", dirname(__FILE__));

# 加载帮助函数模块
include_once PHP_DOTNET . "/php/Utils.php";

# 调试器必须要优先于其他模块进行加载，否则会出现
# Uncaught Error: Class 'dotnetDebugger' not found
# 的错误
include_once PHP_DOTNET . "/Debugger/dotnetException.php";
include_once PHP_DOTNET . "/Debugger/engine.php";
include_once PHP_DOTNET . "/Debugger/view.php";
include_once PHP_DOTNET . "/Debugger/console.php";

# 加载工具框架
include_once PHP_DOTNET . "/System/IO/File.php";
include_once PHP_DOTNET . "/System/Diagnostics/StackTrace.php";
include_once PHP_DOTNET . "/System/Text/StringBuilder.php";
include_once PHP_DOTNET . "/Microsoft/VisualBasic/Strings.php";
include_once PHP_DOTNET . "/Microsoft/VisualBasic/ApplicationServices/Debugger/Logging/LogFile.php";

include_once PHP_DOTNET . "/MSDN.php";

# 加载Web框架部件
include_once PHP_DOTNET . "/RFC7231/index.php";
include_once PHP_DOTNET . "/Registry.php";

# session_start();

# PHP Warning:  date(): It is not safe to rely on the system's timezone settings. 
# You are *required* to use the date.timezone setting or the date_default_timezone_set() function. 
# In case you used any of those methods and you are still getting this warning, you most likely 
# misspelled the timezone identifier. We selected the timezone 'UTC' for now, 
# but please set date.timezone to select your timezone.
date_default_timezone_set('UTC');

/**
 * Global function for load php.NET package 
*/
function Imports($namespace) {
    return dotnet::Imports($namespace, $initiatorOffset = 1);
}

/**
 * 对用户的浏览器进行重定向，支持路由规则
*/
function Redirect($URL) {   
    header("Location: " . Router::AssignController($URL));
}

/**
 * Write session value
*/
function session($name, $value) {
    $_SESSION[$name] = $value;
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
*/
class dotnet {

    public static $error_log;
    public static $debugger;

    /**
     * 函数返回成功消息的json字符串(这个函数只返回json数据，并没有echo输出)
    */ 
    public static function successMsg($msg) {	
		return json_encode([
			'code' => 0,
            'info' => $msg
        ]);
	}
    
    /**
     * 函数返回失败消息的json字符串(这个函数只返回json数据，并没有echo输出)
    */ 
	public static function errorMsg($msg, $errorCode = 1) {
		return json_encode([
			'code' => $errorCode,
            'info' => $msg
        ]);
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
     * 进行php.NET核心模块的加载操作，请注意：在调用这个方法之前需要先使用define函数进行定义APP_DEBUG常数 
     * 
     * @param string|array $config php.NET框架核心正常工作所需要的配置参数，如果忽略掉这个参数的话将会使用默认配置
     *                             在默认配置下，mysql数据库模块将会无法正常工作，因为没有mysql的链接参数信息。
     *                             这个参数可以有两种形式：
     *                             
     *                             1. php文件路径，如果文件不存在，则会使用默认配置数据
     *                             2. 包含有配置数据的字典数组
    */
	public static function AutoLoad($config = NULL) {		       

        if (APP_DEBUG) {
            # 调试器必须先于Imports函数调用，否则会出现错误：
            # PHP Fatal error:  Call to a member function add_loaded_script() on a non-object
            if (!self::$debugger) {
                 self::$debugger = new dotnetDebugger();    
            }            
        }   

		dotnet::Imports("MVC.view");
		dotnet::Imports("MVC.model");
		dotnet::Imports("MVC.router");
        dotnet::Imports("MVC.MySql.driver");       
        dotnet::Imports("MVC.MySql.sqlBuilder");
        dotnet::Imports("MVC.MySql.expression");

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
    
    /**
     * 默认是zhCN中文语言
    */
    public static function GetLanguageConfig() {
        if (array_key_exists("lang", $_GET)) {
			$lang = Strings::LCase($_GET["lang"]);
		} else {
			$lang = "zhCN";
		}

		if ($lang && ($lang === "enus" || $lang === "en") ) {
			$lang = "enUS";
		} else {
			$lang = "zhCN";
		}
		
		return ["lang" => $lang];
    }

    public static function printMySqlTransaction() {
        if (APP_DEBUG) {
            echo debugView::GetMySQLView(self::$debugger);
        }
    }

    /**
     * php写日志文件只能够写在自己的wwwroot文件夹之中 
    */
    public static function writeMySqlLogs($onlyErrors = FALSE) {      
        if (APP_DEBUG) {
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
        $log = "./data/mysql_logs.log";

        FileSystem::WriteAllText($log, "<h5>API: $_SERVER[REQUEST_URI]</h5>\n", TRUE);
        FileSystem::WriteAllText($log, "<ul>" . debugView::GetMySQLView(self::$debugger) . "</ul>\n", TRUE);
    }

    /**
     * 对于这个函数额调用者而言，就是获取调用者所在的脚本的文件夹位置
     * 这个函数是使用require_once来进行模块调用的
     *
     * @param string $mod: 直接为命名空间的路径，不需要考虑相对路径或者添加文件后缀名，例如需要导入VisualBasic的Strings模块的方法，
     *                     只需要调用代码
     * 
     *     dotnet::Imports("Microsoft.VisualBasic.Strings");
     * 
     * @return string 这个函数返回所导入的模块的完整的文件路径
    */
    public static function Imports($mod, $initiatorOffset = 0) {        

        // 因为WithSuffixExtension这个函数会需要依赖小数点来判断文件拓展名，
        // 所以对小数点的替换操作要在if判断之后进行  
        if (Utils::WithSuffixExtension($mod, "php")) {
            $mod = str_replace(".", "/", $mod); 
            $mod = PHP_DOTNET . "/{$mod}";
        } else {
            $mod = str_replace(".", "/", $mod);             

            # 2018-5-15 假若Imports("MVC.view");
            # 因为文件结构之中，有一个view.php和view文件夹
            # 所以在这里会产生冲突
            # 在linux上面因为文件系统区分大小写，所以可以通过大小写来避免冲突
            # 但是windows上面却不可以
            # 在这里假设偏向于加载文件

            $php = PHP_DOTNET . "/{$mod}.php";

            # 如果是文件存在，则只导入文件
            if (File::Exists($php)) {
                $mod = $php;
            } elseif (File::Exists($php = PHP_DOTNET . "/$mod/index.php")) {
                # 如果不存在，则使用index.php来进行判断
                $mod = $php;
            } elseif (is_dir($dir = PHP_DOTNET . "/$mod/")) {
                # 可能是一个文件夹
                # 则认为是导入该命名空间文件夹下的所有的同级的文件夹文件
                return self::importsAll(dirname($mod), $initiatorOffset + 1);
            }
        }        

        self::__imports($mod, $initiatorOffset + 1);

        // 返回所导入的文件的全路径名
        return $mod;
    }

    private static function __imports($mod, $initiatorOffset) {
        // 在这里导入需要导入的模块文件
        include_once($mod);
                
        if (APP_DEBUG) {
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
            if (!self::$debugger) {
                 self::$debugger = new dotnetDebugger();    
            }
            self::$debugger->add_loaded_script($mod, $initiator);
        }
    }

    /**
     * 导入目标命名空间文件夹之下的所有的php模块文件
    */
    private static function importsAll($directory, $initiatorOffset) {

        echo $directory . "\n\n";

        $files = [];
        $dir = opendir($directory);

        while ($dir && ($file = readdir($dir)) !== false) {
            if (Utils::WithSuffixExtension($file, "php")) {
                self::__imports($file, $initiatorOffset + 1);
                array_push($files, $file);
            }
        }

        closedir($dir);

        return $files;
    }

    #region "error codes"

	/**
	 * 500 PHP throw exception helper for show exception in .NET exception style
	*/
    public static function ThrowException($message) {      
		$trace = StackTrace::GetCallStack();
		$exc   = dotnetException::FormatOutput($message, $trace);
				
		RFC7231Error::err500($exc);
		exit(0);
    }

    /**
     * 404 资源没有找到
    */
    public static function PageNotFound($message) {
        $trace = StackTrace::GetCallStack();
		$exc   = dotnetException::FormatOutput($message, $trace);
				
		RFC7231Error::err404($exc);
		exit(0);
    }

    /**
     * 403 用户当前的身份凭证没有访问权限，访问被拒绝
    */
    public static function AccessDenied($message) {
        $trace = StackTrace::GetCallStack();
		$exc   = dotnetException::FormatOutput($message, $trace);
				
		RFC7231Error::err403($exc);
		exit(0);
    }

    #endregion
}
?>