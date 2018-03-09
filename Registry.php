<?php

/**
 * 框架的配置文件的键名列表
 */
class DotNetRegistry {

    // 是否禁用掉框架的错误处理工具
    const ERR_HANDLER_DISABLE = "ERR_HANDLER_DISABLE";
    // 日志文件的文件路径
    const ERR_HANDLER         = "ERR_HANDLER";

    /**
     * 包括mysql数据库的链接参数信息以及框架的设置参数  
     */
    public static $config;
    
    public static function DisableErrorHandler() {
        $hasValue = DotNetRegistry::hasValue(DotNetRegistry::ERR_HANDLER_DISABLE); 
        $optFalse = DotNetRegistry::optFalse(DotNetRegistry::ERR_HANDLER_DISABLE);
        
        return $hasValue && !$optFalse;
    }

    public static function LogFile() {
        $path = "";
        
        if (DotNetRegistry::hasValue(DotNetRegistry::ERR_HANDLER)) {
            $path = DotNetRegistry::$config[DotNetRegistry::ERR_HANDLER];
        } else {
            $path = "./data/php.NET.log";
        }

        return $path;
    }

    /**
     * 如果在配置文件之中设置的参数值为False，则这个函数会返回True，所以可能需要根据上下文添加!操作符进行反义 
     */
    private static function optFalse($key) {
        return DotNetRegistry::$config[$key] == "FALSE"; 
    } 

    private static function hasValue($key) {
        return array_key_exists ($key, DotNetRegistry::$config);
    }
}

?>