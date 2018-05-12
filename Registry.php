<?php

Imports("Microsoft.VisualBasic.Conversion");

/**
 * 框架的配置文件的键名列表
*/
class DotNetRegistry {

    // 是否禁用掉框架的错误处理工具
    const ERR_HANDLER_DISABLE = "ERR_HANDLER_DISABLE";
    // 日志文件的文件路径
    const ERR_HANDLER         = "ERR_HANDLER";
    const MVC_VIEW_ROOT       = "MVC_VIEW_ROOT";
    const DEFAULT_LANGUAGE    = "DEFAULT_LANGUAGE";
    const DEFAULT_AUTH_KEY    = "DEFAULT_AUTH_KEY";

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
     * @param mix $default 默认值，如果键名不存在的话，这个函数默认返回空值
    */
    public static function Read($key, $default = NULL) {
        if (self::hasValue($key)) {
            return self::$config[$key];
        } else {
            return $default;
        }
    }

    public static function DefaultLanguage() {
        return self::Read(DotNetRegistry::DEFAULT_LANGUAGE, "zhCN");
    }

    public static function DefaultAuthKey() {
        return self::Read(DotNetRegistry::DEFAULT_AUTH_KEY, "php-dotnet");
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

        if (self::hasValue(DotNetRegistry::ERR_HANDLER_DISABLE)) {
            return Conversion::CBool(self::$config[DotNetRegistry::ERR_HANDLER_DISABLE]); 
        } else {
            if (APP_DEBUG) {
                return false;
            } else {
                return true;
            }
        }        
    }

    public static function LogFile() {       
        if (self::hasValue(DotNetRegistry::ERR_HANDLER)) {
            return self::$config[DotNetRegistry::ERR_HANDLER];
        } else {
            return "./data/php_errors.log";
        }
    }

    /**
     * 获取html模板文件的文件夹路径
    */
    public static function GetMVCViewDocumentRoot() {
        if (self::hasValue(DotNetRegistry::MVC_VIEW_ROOT)) {
            return self::$config[DotNetRegistry::MVC_VIEW_ROOT];
        } else {
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

        self::$config[DotNetRegistry::MVC_VIEW_ROOT] = $wwwroot;
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