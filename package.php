<?php

# 2018-08-08 在命令行之中快速查找php的配置文件位置

/**
 * echo "<?php echo phpinfo(); ?>" | php | grep php.ini
 *  
*/

#region "Constants"

// APP_DEBUG常数在引用这个文件之前必须首先进行定义

if (!defined('APP_DEBUG')) {
    /**
     * 这个常数会影响框架的调试器的输出行为，默认是关闭调试器
    */
    define("APP_DEBUG", false);
}

if (!defined("FRAMEWORK_DEBUG")) {
    /**
     * 进行框架内部的调试使用的一个常量
    */
    define("FRAMEWORK_DEBUG", false);
}

/**
 * Php script running in a cli environment?
*/
define("IS_CLI", php_sapi_name() === 'cli');

if (IS_CLI && FRAMEWORK_DEBUG) {
    # 2018-10-12 很奇怪，在终端中调试输出的第一行肯定会有一个空格
    # 这个多于的空格会影响输出的格式
    # 在这里跳过第一行
    echo " ------------============ PHP.NET ============-------------\n\n";
    echo " Repository: https://github.com/GCModeller-Cloud/php-dotnet\n";
    echo " Author:     xieguigang <xie.guigang@gcmodeller.org>\n";
    echo "\n\n";
}

if (!defined("SITE_PATH")) {
	if (array_key_exists("DOCUMENT_ROOT", $_SERVER)) {
        /**
         * 当前的网站应用App的wwwroot文档根目录
        */
        define("SITE_PATH", $_SERVER["DOCUMENT_ROOT"]);
    } else {
        # 如果是命令行环境的话，DOCUMENT_ROOT可能不存在
        # 则这个时候就使用当前文件夹
        define("SITE_PATH", getcwd());
    }    
}

if (array_key_exists("REQUEST_METHOD", $_SERVER)) {

	# 2018-09-13 
	# 在命令行环境下，这个值是不存在的，会导致定义失败
	# 所以在这里会需要先判断一下是否存在
	# 如果REQUEST_METHOD不存在的话，则下面的两个常量都不会被定义，
	# 则在下面的代码之中都会被定义为false
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		/**
		 * 当前的访问请求是否是一个POST请求
		*/
		define("IS_POST", true);
		/**
		 * 当前的访问请求是否是一个GET请求
		*/
		define("IS_GET", false);
		
	} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
		/**
		 * 当前的访问请求是否是一个POST请求
		*/
		define("IS_POST", false);
		/**
		 * 当前的访问请求是否是一个GET请求
		*/
		define("IS_GET", true);
	}	
} 

if (!defined("IS_GET") && !defined("IS_POST")) {
	/**
	 * 当前的访问请求是否是一个POST请求
	*/
	define("IS_POST", false);
	/**
	 * 当前的访问请求是否是一个GET请求
	*/
	define("IS_GET", false);
}

#endregion

#region "file_loads"

/**
 * PHP.NET框架的根文件夹位置
 * 获取得到package.php这个文件的所处的文件夹的位置
*/
define("PHP_DOTNET", dirname(__FILE__) . "/Framework");

include_once PHP_DOTNET . "/Debugger/Ubench/Ubench.php";

# 加载框架之中的一些必要的模块，并进行性能计数
$load = new Ubench();
$load->run(function() {

    # 加载核心文件
    include_once PHP_DOTNET . "/dotnet.php";

    # 加载帮助函数模块
    include_once PHP_DOTNET . "/php/Utils.php";
    include_once PHP_DOTNET . "/bootstrap.php";

    # 调试器必须要优先于其他模块进行加载，否则会出现
    # Uncaught Error: Class 'dotnetDebugger' not found
    # 的错误
    include_once PHP_DOTNET . "/Debugger/dotnetException.php";
    include_once PHP_DOTNET . "/Debugger/engine.php";
    include_once PHP_DOTNET . "/Debugger/view.php";
    include_once PHP_DOTNET . "/Debugger/console.php";

    # 加载工具框架
    include_once PHP_DOTNET . "/System/IDisposable.php";
    include_once PHP_DOTNET . "/System/IO/File.php";
    include_once PHP_DOTNET . "/System/Diagnostics/StackTrace.php";
    include_once PHP_DOTNET . "/System/Text/StringBuilder.php";
    include_once PHP_DOTNET . "/Microsoft/VisualBasic/Strings.php";
    include_once PHP_DOTNET . "/Microsoft/VisualBasic/ApplicationServices/Debugger/Logging/LogFile.php";

    include_once PHP_DOTNET . "/MSDN.php";

    # 加载Web框架部件
    include_once PHP_DOTNET . "/RFC7231/index.php";
    include_once PHP_DOTNET . "/Registry.php";
});

#endregion

debugView::LogEvent("--- App start ---");
debugView::LogEvent("Load required modules in " . $load->getTime());

debugView::AddItem("benchmark.load", $load->getTime(true));

# PHP Warning:  date(): It is not safe to rely on the system's timezone settings. 
# You are *required* to use the date.timezone setting or the date_default_timezone_set() function. 
# In case you used any of those methods and you are still getting this warning, you most likely 
# misspelled the timezone identifier. We selected the timezone 'UTC' for now, 
# but please set date.timezone to select your timezone.
date_default_timezone_set('UTC');

#region "global function"

/**
 * Global function for load php.NET package modules.
 * 
 * @param string $namespace php module file path
*/
function Imports($namespace) {
    return dotnet::Imports($namespace);
}

/**
 * 对用户的浏览器进行重定向，支持路由规则。
 * 注意，在使用这个函数进行重定向之后，脚本将会从这里退出执行
*/
function Redirect($URL) {   
    header("Location: " . Router::AssignController($URL));
    exit(0);
}

/**
 * Write session value
 * 
 * @param string $name The session variable name
 * @param mixed $value Value
*/
function session($name, $value) {
    $_SESSION[$name] = $value;
}

function using(\System\IDisposable $obj, callable $procedure) {
    $result = $procedure($obj);
    $obj->Dispose();
    return $result;
}

#endregion