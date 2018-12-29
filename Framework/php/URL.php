<?php

/**
 * URL帮助类
*/
class URL {

    /** 
     * 在URL之中的文件路径部分
     * 
     * @var string
    */
    var $path;
    /** 
     * 在URL之中的参数查询部分
     * 
     * @var array
    */
    var $query;

    /** 
     * @param array $url 必须要有path字段，query字段为可选值
    */
    public function __construct($url) {
        $this->path = $url["path"];
        
        if (array_key_exists("query", $url)) {
            $this->query = $url["query"];
        } else {
            $this->query = [];
        }

        if (empty($this->path)) {
            throw new dotnetException("FileInfo empty!");
        }
    }

    public function __toString() {
        $url = $this->path;

        if (count($this->query) > 0) {
            $url = $url . "?" . self::GetUrlQuery($this->query);
        }

        return $url;
    }

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

    /**
     * UTF-8 aware ``parse_url()`` replacement.
     * 
	 * @param string $url
     * @param boolean $parseURLQuery 是否同时也将query部分解析为数组？默认是不解析，即保持为字符串
     * @return array
    */
    public static function mb_parse_url($url, $parseURLQuery = false, $stdClass = false) {
        $enc_url = preg_replace_callback(
            '%[^:/@?&=#]+%usD',
            function ($matches) {
                return urlencode($matches[0]);
            },
            $url
        );
        
        $parts = parse_url($enc_url);
        
        if ($parts === false) {
            throw new \InvalidArgumentException("Malformed URL: $url");
        }
        
        foreach($parts as $name => $value) {
            $parts[$name] = urldecode($value);
        }
        
        if ($parseURLQuery && array_key_exists("query", $parts)) {
            $parts["query"] = self::ConvertUrlQuery($parts["query"]);
        }

        if ($stdClass) {
            return new URL($parts);
        } else {
            return $parts;
        }
    }
}
