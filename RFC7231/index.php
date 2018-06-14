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
	*/
	public static function getPath($code) {
		return (DotNetRegistry::RFC7231Folder() ?? dirname(__FILE__)) . "/$code.html";
	}
	
	/**
	 * Display an error code page.
	*/
	public static function Display($code, $message, $header = "Unknown") {
		if (!is_integer($code)) {
			$link = MSDN::link("System.Int32");
			dotnet::ThrowException("RFC7231 error code must be an <a href='$link'>System.Int32</a> numeric type!");
		} else {
			header($httpResponse = RFC7231Error::getRFC($code, $header));	
		}	

		View::Show(RFC7231Error::getPath($code), [
			"message" => $message,
			"url"     => Utils::URL(),
			"title"   => $httpResponse
		]);

		exit($code);
	}
	
	private static function getRFC($code, $header = "Unknown") {
		switch ($code) {
			case 404:
				return "HTTP/1.0 404 Not Found";
				break;
			case 403:
				return "HTTP/1.0 403 Forbidden";
				break;
			case 500:
				return "HTTP/1.0 500 Internal Server Error";
				break;

			default:
				return "HTTP/1.0 $code $header";
		}
	}
	
	#region "Framework prefix error code page entry"

	public static function err404($message = NULL) {
		self::Display(404, $message);
	}
	
	public static function err403($message = NULL) {
		self::Display(403, $message);
	}
	
	public static function err500($message = NULL) {
		self::Display(500, $message);
	}

	#endregion
}

?>
