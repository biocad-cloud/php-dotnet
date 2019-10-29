<?php

/**
 * The web request helper
 * 
 * 这个模块主要是用来过滤掉一些可能出现的非法请求字符串
 * 降低被sql注入攻击成功的风险
*/
class WebRequest {

    /**
     * @param string $queryKey
     * 
     * @return string
    */
    public function get($queryKey, $default = null) {
        return Utils::ReadValue($_GET, $queryKey, $default);
    }

    /**
     * @return boolean
    */
    public function getBool($queryKey) {
        $value = Utils::ReadValue($_GET, $queryKey, false);
        // get option value and then 
        // try to convert string to boolean
        return Conversion::CBool($value);
    }

    /**
     * @return integer
    */
    public function getInteger($queryKey, $default = 0) {
        $value = Utils::ReadValue($_GET, $queryKey, $default);
        // get option value and then 
        // try to convert string to integer
        return Conversion::CInt($value);
    }

    /**
     * 读取url查询参数中的值，然后通过分割符分割得到一个字符串数组
     * 
     * @return string[]
    */
    public function getList($queryKey, $delimiter = ",") {
        $value = Utils::ReadValue($_GET, $queryKey, null);

        if (empty($value)) {
            return [];
        } else {
            return explode($delimiter, $value);
        }
    }
}