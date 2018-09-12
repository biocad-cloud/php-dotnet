<?php

Imports("php.Utils");
Imports("MSDN");

/**
 * Custom error page supports
*/
class RFC7231Error {
	
	/**
	 * 这个函数会自动根据注册表之中的配置结果的状态返回相对应的错误代码的页面模版文件
	 * 如果希望能够使用自定义的错误页面，需要在传递给框架的配置数据之中写入``RFC7231``
	 * 字段的值
	 * 
	 * @return string The view document file path.
	*/
	public static function getPath($code, $allow_custom) {
		if ($allow_custom) {
			$custom = DotNetRegistry::RFC7231Folder();
			$dir    = empty($custom) ? dirname(__FILE__) : $custom;
			$view   = "$dir/$code.html";

			if (!file_exists($view)) {
				$view = dirname(__FILE__) . "/$code.html";
			}

			return $view;
		} else {
			return dirname(__FILE__) . "/$code.html";
		}		
	}
	
	/**
	 * Display an error code page.
	*/
	public static function Display($code, $message, 
		$header = "Unknown", 
		$allow_custom = true) {
			
		if (!is_integer($code)) {
			$link = MSDN::link("System.Int32");
			dotnet::ThrowException("RFC7231 error code must be an <a href='$link'>System.Int32</a> numeric type!");
		} else {
			header($httpResponse = RFC7231Error::getRFC($code, $header));	
		}	

		View::Show(RFC7231Error::getPath($code, $allow_custom), [
			"message" => $message,
			"url"     => Utils::URL(),
			"title"   => $httpResponse
		]);

		exit($code);
	}
	
	public static $httpErrors = [
		"404" => "Not Found",
		"403" => "Forbidden",
		"500" => "Internal Server Error",
		"429" => "Too Many Requests"
	]; 

	/**
	 * @return string The http status code header
	*/
	private static function getRFC($code, $header = "Unknown") {
		$code = strval($code);

		if (array_key_exists($code, self::$httpErrors)) {
			$msg = self::$httpErrors[$code];
		} else {
			$msg = $header;
		}

		return "HTTP/1.0 $code $msg";		
	}
	
	#region "Framework prefix error code page entry"

	public static function err404($message = NULL, $allow_custom = true) {
		self::Display(404, $message, "", $allow_custom);
	}
	
	public static function err403($message = NULL, $allow_custom = true) {
		self::Display(403, $message, "", $allow_custom);
	}
	
	public static function err500($message = NULL, $allow_custom = true) {
		self::Display(500, $message, "", $allow_custom);
	}

	public static function err429($message = NULL, $allow_custom = true) {
		self::Display(429, $message, "", $allow_custom);
	}
	#endregion
}

?>
