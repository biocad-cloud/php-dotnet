<?php

/**
 * URL帮助类
*/
class URL {

    /**
     * 将字符串参数变为数组
     * 
     * @param string $query
     * @return array array (size=10)
        'm'      => string 'content' (length=7)
        'c'      => string 'index'   (length=5)
        'a'      => string 'lists'   (length=5)
        'catid'  => string '6' (length=1)
        'area'   => string '0' (length=1)
        'author' => string '0' (length=1)
        'h'      => string '0' (length=1)
        'region' => string '0' (length=1)
        's'      => string '1' (length=1)
        'page'   => string '1' (length=1)
    */
    public static function ConvertUrlQuery($query) {
        $queryParts = explode('&', $query);
        $params     = [];

        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }

        return $params;
    }

    /**
     * 将参数变为字符串
     * 
     * @param array $query
     * @return string 'm=content&c=index&a=lists&catid=6&area=0&author=0&h=0®ion=0&s=1&page=1' (length=73)
    */
    public static function GetUrlQuery($query) {
        $tmp = [];

        foreach($query as $k => $param) {
            $tmp[] = "$k=$param";
        }

        return implode('&', $tmp);
    }
}