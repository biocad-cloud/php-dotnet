<?php

/**
 * The web request helper
 * 
 * 这个模块主要是用来过滤掉一些可能出现的非法请求字符串
 * 降低被sql注入攻击成功的风险
*/
class WebRequest {

    /**
     * Get request query value from ``$_GET`` or ``$_POST`` if the http request is a post request 
     * 
     * @param string $queryKey
     * 
     * @return string
    */
    public static function get($queryKey, $default = null) {
        if (array_key_exists($queryKey, $_GET)) {
            return $_GET[$queryKey];
        } else if (IS_POST && array_key_exists($queryKey, $_POST)) {
            return $_POST[$queryKey];
        } else {
            return $default;
        }
    }

    public static function is_pattern($queryKey, $pattern) {
        $str = self::get($queryKey);

        if (empty($str)) {
            return false;
        } else {
            return StringHelpers::IsPattern($str, $pattern);
        }
    }

    /**
     * 查看url查询或者POST数据之中是否存在目标数据
     * 
     * @param string $queryKey
     * @param boolean $empty_as_missing 如果这个参数为真的话，数字0会被当作为空返回false
     * 
     * @return boolean
    */
    public static function has($queryKey, $empty_as_missing = TRUE) {
        if (array_key_exists($queryKey, $_GET)) {
            return $empty_as_missing ? $_GET[$queryKey] != "" : TRUE;
        } else if (IS_POST && array_key_exists($queryKey, $_POST)) {
            return $empty_as_missing ? $_POST[$queryKey] != "" : TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Get a logical value 
     * 
     * @param string $queryKey the url parameter name.
     * 
     * @return boolean
    */
    public static function getBool($queryKey) {
        if (!self::has($queryKey, false)) {
            return false;
        } else {
            $value = self::get($queryKey, false);
            // get option value and then 
            // try to convert string to boolean
            return Conversion::CBool($value);
        }
    }

    /**
     * 从url查询之中获取得到值并解析为整形数
     * 
     * @param string $queryKey
     * @param integer $default 当查询参数不存在或者不满足约束条件的时候的默认值
     * @param boolean $unsigned 约束规则：目标结果值是否应该为正实数
     * 
     * @return integer
    */
    public static function getInteger($queryKey, $default = 0, $unsigned = true) {
        $value = self::get($queryKey, $default);
        // get option value and then 
        // try to convert string to integer
        $i32 = Conversion::CInt($value);

        if ($unsigned && $i32 < 0) {
            return $default;
        } else {
            return $i32;
        }
    }

    public static function getNumeric($queryKey, $default = 0.0, $unsigned = true) {
        $value = self::get($queryKey, $default);
        // get option value and then 
        // try to convert string to integer
        $f64 = Conversion::CDbl($value);

        if ($unsigned && $f64 < 0) {
            return $default;
        } else {
            return $f64;
        }
    }

    /**
     * Get a file path components that comes from the url query 
     * parameter or post arguments
     * 
     * @param boolean $raw If required raw value, then the un-processed query data will be returned
     *     otherwise the trimmed and urldecoded string data will be returned.
    */
    public static function getPath($queryKey, $default = NULL, $raw = FALSE) {
        $value = self::get($queryKey, $default);
        
        if (empty($value)) {
            return $default;
        } else {
            if ($raw) {
                return $value;
            } else {
                $value = urldecode($value);
                # strip parent path visits
                # avoid unexpected file access problem.
                # pattern for visit parent path is /../
                $value = str_replace('/../', "/", $value);
                $value = ltrim($value, "./");

                return $value;
            }
        }
    }

    /**
     * 读取url查询参数中的值，然后通过分割符分割得到一个字符串数组
     * 
     * @return string[]
    */
    public static function getList($queryKey, $delimiter = ",") {
        $value = self::get($queryKey, null);

        if (empty($value)) {
            return [];
        } else {
            return explode($delimiter, $value);
        }
    }
}

/**
 * Web response helper
*/
class WebResponse {

    /**
     * send content type http header
     * 
     * @param string $mime Set content-type
    */
    public static function content_type($mime) {
        header("Content-Type: $mime");
    }

    /**
     * send file content to client browser
     * 
     * @param string $path a valid file path.
     * @param string $mime Set content-type
    */
    public static function sendContent($path, $mime) {
        Utils::PushDownload($path, -1, $mime);
    }
}