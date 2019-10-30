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
     * 从url查询之中获取得到值并解析为整形数
     * 
     * @param string $queryKey
     * @param integer $default 当查询参数不存在或者不满足约束条件的时候的默认值
     * @param boolean $unsigned 约束规则：目标结果值是否应该为正实数
     * 
     * @return integer
    */
    public function getInteger($queryKey, $default = 0, $unsigned = true) {
        $value = Utils::ReadValue($_GET, $queryKey, $default);
        // get option value and then 
        // try to convert string to integer
        $i32 = Conversion::CInt($value);

        if ($unsigned && $i32 < 0) {
            return $default;
        } else {
            return $i32;
        }
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