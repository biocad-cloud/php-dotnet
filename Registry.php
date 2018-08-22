<?php

Imports("Microsoft.VisualBasic.Conversion");

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
define("MVC_VIEW_ROOT",    "MVC_VIEW_ROOT");
define("DEFAULT_LANGUAGE", "DEFAULT_LANGUAGE");
define("DEFAULT_AUTH_KEY", "DEFAULT_AUTH_KEY");

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
     * 获取自定义错误代码页面的文件夹存放路径
    */
    public static function RFC7231Folder() {
        $dir = self::Read(RFC7231, null);

        if (!$dir) {
            return null;
        }
        
        if (!defined('APP_PATH')) {
            return $dir;
        } else {
            return APP_PATH . "/" . $dir;
        }
    }

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
        return array();
    }

    public static function DisableErrorHandler() {    

        # 当没有定义配置参数的时候，会根据是否处于调试模式来返回flag状态
        # 如果没有定义配置参数，则在调试模式下永远禁用，即将在调试模式下所有的错误都显示在页面上
        # 如果没有定义配置参数，则在非调试模式下永远启用，即将错误信息写入到log文件之中 

        if (self::hasValue(ERR_HANDLER_DISABLE)) {
            return Conversion::CBool(self::$config[ERR_HANDLER_DISABLE]); 
        } else {
            if (APP_DEBUG) {
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
        $script = $_SERVER["SCRIPT_FILENAME"];
        $script = explode("/", $script);
        $script = $script[count($script) - 1];
        $script = explode(".", $script)[0];

        $config = self::Read(MVC_VIEW_ROOT);

        if (empty($config) || count($config) == 0) {
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

    public static function ConfigIsNothing() {
        return is_int(self::$config) || !self::$config;
    }

    public static function SetMVCViewDocumentRoot($wwwroot) {
        if (self::ConfigIsNothing()) {
            self::$config = array();
        }       

        self::$config[MVC_VIEW_ROOT] = $wwwroot;
    }

    /**
     * 如果在配置文件之中设置的参数值为False，则这个函数会返回True，所以可能需要根据上下文添加!操作符进行反义 
    */
    private static function optFalse($key) {
        if (self::hasValue($key)) {
            return Conversion::CBool(self::$config[$key]) === false; 
        } else {
            # 键值对不存在，则肯定是False，返回True表示当前的键值是False
            return true;
        }        
    } 

    private static function hasValue($key) {
        $config = self::$config;

        if (is_array($config)) {
            return array_key_exists ($key, $config);
        } else {
            return false;
        }
    }
}

?>