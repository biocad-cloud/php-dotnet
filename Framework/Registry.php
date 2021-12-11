<?php

imports("Microsoft.VisualBasic.Conversion");

#region ".NET Registry keys"

/**
 * 是否禁用掉框架的错误处理工具
*/
define("ERR_HANDLER_DISABLE", "ERR_HANDLER_DISABLE");
/**
 * 日志文件的文件路径
*/
define("ERR_HANDLER",         "ERR_HANDLER");
/**
 * 自定义错误页面的文件夹位置，403 404 500等
*/
define("RFC7231",             "RFC7231");

# MVC
/**
 * 默认的模板文档的文件夹为根文件夹下面的命名为html的文件夹
*/
define("MVC_VIEW_ROOT",    "MVC_VIEW_ROOT");
define("DEFAULT_LANGUAGE", "DEFAULT_LANGUAGE");
define("DEFAULT_AUTH_KEY", "DEFAULT_AUTH_KEY");

/** 
 * 在路由器模块之中是否启用模板之中的URL重写功能
*/
define("REWRITE_ENGINE",   "REWRITE_ENGINE");

#endregion

/**
 * 框架的配置文件的键名列表
*/
class DotNetRegistry {

    /**
     * 包括mysql数据库的链接参数信息以及框架的设置参数  
     * 
     * 在这个数组之中包含有一个默认的数据库连接参数配置
     * 以及一个多数据库配置数组信息
    */
    public static $config;
    
    /**
     * 从框架的配置文件注册表之中读取一个配置值
     * 
     * @param string $key 键名
     * @param mixed $default 默认值，如果键名不存在的话，这个函数默认返回空值
    */
    public static function Read($key, $default = NULL) {
        if (self::hasValue($key)) {
            return self::$config[$key];
        } else {
            return $default;
        }
    }

    /** 
     * 是否打开路由器的URL重写功能？
     * 
     * 注意，这个配置项是会受到网站的根目录下的``.htaccess``文件的影响的：
     * 
     * + 即使在网站的配置文件之中设定了重写引擎打开，但是``.htaccess``文件不存在于网站的根目录下，则路由器模块会报错
     * + 即使在网站的配置文件之中设定了重写引擎打开，但是``.htaccess``文件之中指示RewriteEngine的配置为Off，则路由器不会启用重写功能，并且给出一条警告消息
     * + ``.htaccess``文件指示RewriteEngine的配置为On，则用户访问web服务器可能会发生重写，但是如果网站配置之中设定重写引擎关闭，则html模板之中的url将不会被重写，并给出一条警告消息
     * + ``.htaccess``文件指示RewriteEngine的配置为On，则用户访问web服务器可能会发生重写，如果网站配置之中设定开启重写引擎，则html模板之中的url将会根据配置被重写
     * 
     * @return boolean
    */
    public static function RewriteEngine() {

    }

    /** 
     * Do html minifier of the cache page its content text?
    */
    public static function HtmlMinifyOfCache() {
        return !self::optFalse("CACHE.MINIFY");
    }

    public static function AppName($default = "php.net") {
        return self::Read("APP_NAME", $default);
    }

    /**
     * 获取自定义错误代码页面的文件夹存放路径
    */
    public static function RFC7231Folder() {
        $dir = self::Read(RFC7231, null);

        if (!$dir) {
            return null;
        }
        
        if (!defined('APP_PATH')) {
            return $dir;
        } else if (file_exists($dir) && is_dir($dir)) {
            return $dir;
        } else {
            return APP_PATH . "/" . $dir;
        }
    }

    /**
     * 获取网页显示的语言默认配置值，默认配置语言是中文语言``zhCN``。
    */
    public static function DefaultLanguage() {
        return self::Read(DEFAULT_LANGUAGE, "zhCN");
    }

    public static function DefaultAuthKey() {
        return self::Read(DEFAULT_AUTH_KEY, "php-dotnet");
    }

    /**
     * The default config file data.
    */
    public static function DefaultConfig() {
        return [];
    }

    /** 
     * 函数返回TRUE，表示禁用框架的错误报告系统，直接将所有的错误消息输出到页面或者终端上
     * 返回FALSE，表示不禁用当前框架的错误报告系统，错误消息将会被输出到调控终端上面
     * 
     * @return boolean 
    */
    public static function DisableErrorHandler() {

        # 当没有定义配置参数的时候，会根据是否处于调试模式来返回flag状态
        # 如果没有定义配置参数，则在调试模式下永远禁用，即将在调试模式下所有的错误都显示在页面上
        # 如果没有定义配置参数，则在非调试模式下永远启用，即将错误信息写入到log文件之中 

        if (self::hasValue(ERR_HANDLER_DISABLE)) {
            return Conversion::CBool(self::$config[ERR_HANDLER_DISABLE]); 
        } else {
            if (APP_DEBUG || FRAMEWORK_DEBUG) {
                return false;
            } else {
                return true;
            }
        }        
    }

    public static function LogFile() {
        return self::Read(ERR_HANDLER, "./data/php_errors.log");
    }

    /**
     * 获取html模板文件的文件夹路径
     * 
     * 相关的配置项可以直接是文件夹路径字符串，或者字典数组
     * 假若是字典数组的话，则要求数组的键名应该是脚本的不带有拓展名的文件名
     * 键值则是该键名所对应的html模板文件的文件夹路径
     * 
     * @return string html模板文件的文件夹路径字符串值
    */
    public static function GetMVCViewDocumentRoot() {
        $config = self::Read(MVC_VIEW_ROOT);
        $script = self::GetInitialScriptName();
        
        if (empty($config) || (is_array($config) && count($config) == 0)) {
            # 是空的，则返回默认路径
            return "./html";
        } else if (is_string($config) && strlen($config) > 0) {
            # 配置的值是一个字符串，则直接返回
            return $config;
        } else if (is_array($config) && array_key_exists($script, $config)) {
            # 是一个数组，并且配置项存在
            return $config[$script];
        } else {
            # 是一个数组，但是配置项不存在，则使用默认
            return "./html";
        }
    }

    /**
     * 如果配置只是一个文件夹路径字符串，则会使用一个键名为``*``的字典数组返回值
    */
    public static function GetMVCViewDocumentRootTable() {
        $config = self::Read(MVC_VIEW_ROOT);

        if (empty($config)) {
            return [];
        } else if (is_string($config)) {
            return ["*" => $config];
        } else {
            return $config;
        }
    }

    /**
     * 获取得到当前的用户请求的最开始的脚本文件的文件名
     * 
     * @return string 不包含拓展名的脚本文件名
    */
    public static function GetInitialScriptName() {
        $script = $_SERVER["SCRIPT_FILENAME"];
        $script = explode("/", $script);
        $script = $script[count($script) - 1];
        $script = explode(".", $script)[0];

        return $script;
    }

    /**
     * 这个函数判断当前的配置文件是否是空值？
    */
    public static function ConfigIsNothing() {
        return is_int(self::$config) || !self::$config;
    }

    public static function SetMVCViewDocumentRoot($wwwroot) {
        if (self::ConfigIsNothing()) {
            self::$config = array();
        } 
        if (!array_key_exists(MVC_VIEW_ROOT, self::$config)) {
            self::$config[MVC_VIEW_ROOT] = [];
        }

        self::$config[MVC_VIEW_ROOT][self::GetInitialScriptName()] = $wwwroot;
    }

    /**
     * 如果在配置文件之中设置的参数值为False，则这个函数会返回True，
     * 所以可能需要根据上下文添加!操作符进行反义
     * 
     * @param string $key 配置文件数据之中的一个配置项的字符串名称
     * 
     * @return boolean 如果配置项在配置文件之中不存在或者其逻辑值或者其字符串值可以
     *   被解释为``false``的话，则这个函数会返回``true``，表示该所给定的配置项在配置
     *   文件之中的配置值为逻辑值``false``，反之这个函数返回``false``，表示其配置值
     *   不是``false``
    */
    private static function optFalse($key) {
        if (self::hasValue($key)) {
            return Conversion::CBool(self::$config[$key]) === false; 
        } else {
            # 键值对不存在，则肯定是False，返回True表示当前的键值是False
            return true;
        }        
    } 

    /**
     * 判断所给定的配置项在配置文件之中是否具有值?
     * 
     * @param string $key 配置文件数据之中的一个配置项的字符串名称
     * 
     * @return boolean 如果所给定的配置项在配置文件之中不存在的话，函数会返回``false``，
     *     反之返回``true``则表示该配置项存在于配置文件之中。
    */
    private static function hasValue($key) {
        $config = self::$config;

        if (is_array($config)) {
            return array_key_exists ($key, $config);
        } else {
            return false;
        }
    }
}