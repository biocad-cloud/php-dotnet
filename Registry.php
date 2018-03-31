<?php

/**
 * 框架的配置文件的键名列表
 */
class DotNetRegistry {

    // 是否禁用掉框架的错误处理工具
    const ERR_HANDLER_DISABLE = "ERR_HANDLER_DISABLE";
    // 日志文件的文件路径
    const ERR_HANDLER         = "ERR_HANDLER";
    const MVC_VIEW_ROOT       = "MVC_VIEW_ROOT";

    /**
     * 包括mysql数据库的链接参数信息以及框架的设置参数  
     */
    public static $config;
    
    public static function DisableErrorHandler() {
        $hasValue = self::hasValue(DotNetRegistry::ERR_HANDLER_DISABLE); 
        $optFalse = self::optFalse(DotNetRegistry::ERR_HANDLER_DISABLE);
        
        return $hasValue && !$optFalse;
    }

    public static function LogFile() {       
        if (self::hasValue(DotNetRegistry::ERR_HANDLER)) {
            return self::$config[DotNetRegistry::ERR_HANDLER];
        } else {
            return "./data/php_errors.html";
        }
    }

    /**
     * 获取html模板文件的文件夹路径
     * 
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
        return self::$config[$key] == "FALSE"; 
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