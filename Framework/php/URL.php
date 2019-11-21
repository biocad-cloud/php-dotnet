<?php

/**
 * The php url data helper.
 * (URL帮助类)
*/
class URL {

    /** 
     * 在URL之中的文件路径部分
     * 
     * @var string
    */
    var $path;
    /** 
     * 在URL之中的参数查询部分，是一个键值对数组
     * 
     * @var array
    */
    var $query;

    /** 
     * php.NET框架之中的url模式之中的专有控制器函数名称
     * 
     * @var string
    */
    var $app;

    public function getScriptName() {

    }

    /** 
     * @param array $url 必须要有path字段，query字段为可选值
    */
    public function __construct($url) {
        $this->path = $url["path"];
        
        if (array_key_exists("query", $url)) {
            $this->query = $url["query"];
            $this->app   = Utils::ReadValue($this->query, "app", "index");
        } else {
            $this->query = [];
        }

        if (empty($this->path)) {
            throw new dotnetException("FileInfo empty!");
        }
    }

    /** 
     * 比较两个url对象之间的模式是否是一样的
     * 
     * @param URL $url
     * 
     * @return boolean 这个比较函数不会比较查询参数的值
    */
    public function PatternEquals($url, $strict = true) {
        if ($url->path !== $this->path) {
            return false;
        }

        $c1 = count($this->query);
        $c2 = count($url->query);

        if ($c1 > 0 && $c2 > 0) {
            if ($strict && ($c1 !== $c2)) {
                return false;
            } 

            # 非严格模式下，只需要url里面具有当前url中的查询参数集合
            // 判断对应的参数名是否都存在
            foreach($this->query as $name => $any) {
                if (!array_key_exists($name, $url->query)) {
                    return false;
                }
            }

            return true;
        } else if ($c1 == 0 && $c2 == 0) {
            return true;
        } else {
            if ($strict) {
                return false;
            } else {
                return true;
            }
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
	 * @param string $url 默认为当前的URL
     * @param boolean $parseURLQuery 是否同时也将query部分解析为数组？默认是不解析，即保持为字符串
     * @return URL 如果$stdClass参数为true的话，则会返回一个对象而不是一个数组。这个函数默认返回一个数组
    */
    public static function mb_parse_url($url = null, $parseURLQuery = false, $stdClass = false) {
        if (Strings::Empty($url)) {
            $url = Utils::URL(false);
        }
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

    /** 
     * 将重写之间的url查询参数写入$_GET中
     * 
     * 因为URL重写之后，有一部分的查询参数可能会不存在于重写后的URL中
     * 所以会需要用这个函数将重写前的参数写入$_GET数组中来解决这个问题
    */
    public static function NormalizeRedirectArguments() {
        if (IS_CLI) {
            return;
        } else {
            $args = self::mb_parse_url($_SERVER["REQUEST_URI"], true);
        }

        foreach($args as $name => $val) {
            if (!array_key_exists($name, $_GET)) {
                $_GET[$name] = $val;
            }
        }

        if (array_key_exists("query", $args)) {
            foreach($args["query"] as $name => $val) {
                if (!array_key_exists($name, $_GET)) {
                    $_GET[$name] = $val;
                }
            }
        }
    }
}
