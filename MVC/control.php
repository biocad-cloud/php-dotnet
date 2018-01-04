<?php

/*
 * REST api handler
 *
 * 这个模块的功能主要是解析所请求的url字符串，然后从
 */
class Control {
	
	public static $debug;
	
	/*
	 * 进行自动处理请求主要是用户通过实例化一个class之后
	 * 这个函数会对url的解析结果从class实例对象之中匹配出
     * 相同的函数名然后进行调用	 
	 *
	 */
	public static function HandleRequest($app) {
		$argv = $_GET;
		
		if (!$_GET || count($_GET) == 0) {
			# index.html as default
			$page = "index";
		} else {
			$page = $_GET["app"];
		}		
		
		if (self::$debug) {
			# print_r($argv);
			# print_r($page);
		}
		
		$app->{$page}();
	}
		
	/**
     * UTF-8 aware parse_url() replacement.
     * 
     * @return array
     */
    private static function mb_parse_url($url) {
        $enc_url = preg_replace_callback(
            '%[^:/@?&=#]+%usD',
            function ($matches)
            {
                return urlencode($matches[0]);
            },
            $url
        );
        
        $parts = parse_url($enc_url);
        
        if($parts === false)
        {
            throw new \InvalidArgumentException('Malformed URL: ' . $url);
        }
        
        foreach($parts as $name => $value)
        {
            $parts[$name] = urldecode($value);
        }
        
        return $parts;
    }

    public static function successMsg($msg) {	
		return json_encode(array(
			'code' => 0,
			'info' => $msg));
	}
	
	public static function errorMsg($msg, $errorCode = 1) {
		return json_encode(array(
			'code' => $errorCode,
			'info' => $msg));
	}
}

?>