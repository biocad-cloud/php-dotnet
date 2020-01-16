<?php

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

    /**
     * @var dotnetDebugger
    */
    public static $debugger;

    /** 
     * @var controller
    */
    public static $controller;

    /**
     * 函数返回成功消息的json字符串``{code: 0, info: $msg}``.
     * (这个函数只返回json数据，并没有echo输出)
     * 
     * @param string|array $msg The message that will be responsed to 
     *                          the http client.
     * 
     * @return string The success message json with `code` is ZERO 
    */ 
    public static function successMsg($msg) {
		return json_encode([
			'code' => 0,
            'info' => $msg
        ]);
	}
    
    /**
     * 函数返回失败消息的json字符串``{code: 0, info: $msg}``.
     * (这个函数只返回json数据，并没有echo输出)
     * 
     * @param integer $errorCode Default error code is 1. And zero for no error.
     * @param string|array $msg The message that will be responsed to 
     *                          the http client.
     * 
    */ 
	public static function errorMsg($msg, $errorCode = 1, $debug = null) {
        if (empty($debug)) {
            return json_encode([
                'code' => $errorCode,
                'info' => $msg
            ]);
        } else {
            return json_encode([
                'code'  => $errorCode,
                'info'  => $msg,
                "debug" => $debug
            ]);
        }
	}

    /**
     * Handle web http request
     * 
     * 使用这个函数来进行web请求的处理操作
     * 
     * @example 函数的第一个参数必须是一个class对象，第二个参数为html模板的文件路径或者控制器对象
     *          如果指定了wwwroot html模板文件夹，想要再挂载控制器对象的话，可以将控制器对象的
     *          实例传递到函数的第三个参数
     * 
     *    dotnet::HandleRequest(new App());
     *    dotnet::HandleRequest(new App(), "./");
     *    dotnet::HandleRequest(new App(), new accessControl());
     *    dotnet::HandleRequest(new App(), "./", new accessControl());
     * 
     * @param object $app The web app logical layer
     * @param string|controller $wwwroot The html views document root directory.
     * @param controller $injection The access control injection.
    */
    public static function HandleRequest($app, $wwwroot = NULL, $injection = NULL) {
        if ($wwwroot && is_string($wwwroot)) {
            DotNetRegistry::SetMVCViewDocumentRoot($wwwroot);
            debugView::LogEvent("SetMVCViewDocumentRoot => $wwwroot");
        } else if ($wwwroot && is_object($wwwroot)) {
            $injection = $wwwroot;
        }

        # 如果当前的服务器资源上面存在访问控制器的话，则进行用户权限的控制
        if ($injection) {
            debugView::LogEvent("Hook controller");
            self::$controller = $injection->Hook($app);

            # 用户访问权限控制
            if (!$injection->accessControl()) {
                 $injection->Redirect(403);
                 exit(403);

            # 服务器资源访问量的限制
            } else if ($injection->Restrictions()) {
                 $injection->Redirect(429);
                 exit(429);

            # 可以正常访问
            } else {
                global $_DOC;
                $_DOC = $injection->getDocComment();
            }

            debugView::LogEvent("[Begin] Handle user request");

            $injection->sendContentType();
            $injection->handleRequest();

        } else {
            // 没有定义控制器的时候，使用router进行访问请求的处理操作

            // 具有访问权限的正常访问
            Router::HandleRequest($app);
        }       
    }

    /**
     * This method have not implemented yet!
     * 
     * Usage:
     *      ``die(dotnet::$MethodNotImplemented);``
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
        $initiator = new Ubench();
        $initiator->start();

        if (APP_DEBUG) {
            # 调试器必须先于Imports函数调用，否则会出现错误：
            # PHP Fatal error:  Call to a member function add_loaded_script() on a non-object
            if (!self::$debugger) {
                 self::$debugger = new dotnetDebugger();
            }            
        }   

        # 在这里加载框架之中的基本的MVC驱动程序模块
		dotnet::Imports("MVC.view");
		dotnet::Imports("MVC.model");
        dotnet::Imports("MVC.router");
        dotnet::Imports("MVC.request");
        dotnet::Imports("MVC.MySql.driver");
        dotnet::Imports("MVC.MySql.expression");
        dotnet::Imports("php.URL");

        URL::NormalizeRedirectArguments();

        if ($config) {
            # config存在赋值，则判断一下是否为字符串？
            # 如果是字符串，则使用文件的加载方式
            # 反之再判断是否为数组
            # 如果既不是字符串又不是数组，则使用默认配置数据并给出警告
            if (is_string($config)) {
                if (file_exists($config)) {
                    # load configuration file from a given config file.
                    DotNetRegistry::$config = include $config;
                    # debug echo on cli
                    console::log("Load framework config file from '" . realpath($config) . "'");
                } else {
                    # file not exists!
                    console::warn("Config data php file '$config' is not exists on your filesystem...");
                    # load default configuration data.
                    DotNetRegistry::$config = DotNetRegistry::DefaultConfig();
                }
            } elseif (is_array($config)) {
                DotNetRegistry::$config = $config;
            } else {
                # 无效的配置参数信息，使用默认的配置并且给出警告信息
                console::warn("A config data was given, but data type is mismatched, require a php file path or config data array.");
                # load default configuration data.
                DotNetRegistry::$config = DotNetRegistry::DefaultConfig();
            }
        } else {
            DotNetRegistry::$config = DotNetRegistry::DefaultConfig();
        }

        if (!DotNetRegistry::DisableErrorHandler()) {
            self::setupLogs();
        }

        $initiator->end();

        debugView::$showStackTrace = DotNetRegistry::Read("show.stacktrace", true);
        debugView::LogEvent("App init in " . $initiator->getTime());
        debugView::AddItem("benchmark.init", $initiator->getTime(true));
    }
    
    private static function setupLogs() {
        // Report all PHP errors (see changelog)
        error_reporting(E_ALL);
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            # 2018-3-5 Call to a member function LoggingHandler() on a non-object
            // $logs = dotnet::$error_log;
            //$logs->LoggingHandler($errno, $errstr, $errfile, $errline);
            console::error_handler($errno, $errstr, $errfile, $errline);
        }, E_ALL);
    }
    
	/**
	 * 获取目标html文档梭对应的缓存文件的文件路径: ``temp/appname``
     * 
     * 因为这个文件夹可能会因为清除缓存的原因被删除掉，所以在这个文件夹之中不应该持久性的存储重要的数据文件
     * 
     * @return string 缓存文件夹的路径字符串
	*/
	public static function getMyTempDirectory() {
		$temp = sys_get_temp_dir();

		if (strtolower($temp) == strtolower("C:\\Windows")) {
			# 不可以写入Windows文件夹
            # 写入自己的data文件夹下面的临时文件夹
            # 因为是写在自己的文件夹之中了，所以在这里就不用再加appName了
			if (defined("APP_PATH")) {
				$temp = APP_PATH . "/data/cache";
			} else {
				$temp = "./data/cache";
			}			
		} else {
            $appName = DotNetRegistry::AppName();
            $temp    = "$temp/$appName"; 
        }

		return $temp;
    }
    
    /**
     * 删除缓存文件夹
    */
    public static function DeleteCache() {
        $cache = self::getMyTempDirectory();

        if (empty($cache) || $cache == "/" || $cache == "C:\\Windows") {
            # 系统的根目录
            # 不做任何操作
        } else {
            unlink($cache);
        }
    }

    /**
     * 获取得到当前的语言配置
     * 
     * 这个函数会从GET参数或者cookie之中获取语言配置信息，默认是``zhCN``中文语言
     * 
     * @return array ``["lang" => languageName]``
    */
    public static function GetLanguageConfig() {
        if (array_key_exists("lang", $_GET)) {
            $lang = Strings::LCase($_GET["lang"]);
        } else if (array_key_exists("lang", $_COOKIE)) {
            $lang = Strings::LCase($_COOKIE["lang"]);
		} else {
			$lang = "zhCN";
		}

		if ($lang && ($lang === "enus" || $lang === "en" || $lang === "en-us")) {
			$lang = "enUS";
		} else {
			$lang = "zhCN";
		}
		
		return ["lang" => $lang];
    }

    /**
     * 对于这个函数额调用者而言，就是获取调用者所在的脚本的文件夹位置
     * 这个函数是使用``require_once``来进行模块调用的
     *
     * @param string $module: 直接为命名空间的路径，不需要考虑相对路径或者添加文件后缀名，例如需要导入VisualBasic的Strings模块的方法，
     *                     只需要调用代码
     * 
     *     ``imports("Microsoft.VisualBasic.Strings");``
     * 
     * @return string 这个函数返回所导入的模块的完整的文件路径
    */
    public static function Imports($module) {
        // 在这里需要添加加载记录
        // 否则isloaded函数任然会判断目标模块没有被加载
        bootstrapLoader::push($module, []);
        // 进行模块引用的预处理
        // 然后执行解析出来的php文件的加载操作
        return \PhpDotNet\bootstrap::LoadModule($module);
    }

    #region "error codes"

    private static function exceptionMsg($message) {
        if (debugView::$showStackTrace) {
            $trace = StackTrace::GetCallStack();
            $exc   = dotnetException::FormatOutput($message, $trace);
        } else {
            $exc   = $message;
        }

        return $exc;
    }

	/**
	 * 500 PHP throw exception helper for show exception in .NET 
     * exception style
     * 
     * @param string $message The error message to display.
	*/
    public static function ThrowException($message) {			
		RFC7231Error::err500(self::exceptionMsg($message));
		exit(500);
    }

    /**
     * 404 资源没有找到
     * 
     * @param string $message The error message to display.
    */
    public static function PageNotFound($message) {				
		RFC7231Error::err404(self::exceptionMsg($message));
		exit(404);
    }

    /**
     * 403 用户当前的身份凭证没有访问权限，访问被拒绝
     * 
     * @param string $message The error message to display.
    */
    public static function AccessDenied($message) {
		RFC7231Error::err403(self::exceptionMsg($message));
		exit(403);
    }

    /** 
     * 405 Method not allowed 方法不被允许
     * 
     * 例如，某控制器被标记为POST方法，但是http请求为GET请求，就会触发这个405错误
     * 
     * @param string $message The error message to display.
    */
    public static function InvalidHttpMethod($message) {				
		RFC7231Error::err405(self::exceptionMsg($message));
		exit(405);
    }

    /**
     * 429 请求次数过多
    */
    public static function TooManyRequests($message) {			
		RFC7231Error::err429(self::exceptionMsg($message));
		exit(429);
    }

    public static function BadRequest($message) {	
		RFC7231Error::err400(self::exceptionMsg($message));
		exit(400);
    }
    #endregion
}