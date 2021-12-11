<?php

imports("php.Utils");
imports("MSDN");
imports("RFC7231.logger");

/**
 * Custom error page supports
*/
class RFC7231Error {
	
	/**
	 * a callback function with two parameters: 
	 *    http status code/error message
	 * 
	 * @var \logger
	*/
	public static $logger;

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
	 * 
	 * @param $allow_custom enable the user custom error page template?
	 * @param $dohttpheader send the http error code in http headers?
	*/
	public static function Display($code, $message, 
		$header = "Unknown", 
		$allow_custom = true, 
		$dohttpheader = true) {
			
		if (!IS_CLI) {
			ob_end_clean();
		}

		if (!is_integer($code)) {
			$msg = "Error code must be an " . \PhpDotNet\MSDN::link("System.Int32") . " numeric type!";
			dotnet::ThrowException($msg);
		} else {
			$httpResponse = RFC7231Error::getRFC($code, $header);

			if ((!IS_CLI) && $dohttpheader) {
				header($httpResponse);	
			}
		}	

		if (!Utils::isDbNull(self::$logger)) {
			self::$logger->log($code, $message);
		}

		View::Push("description", $httpResponse);
		View::Show(RFC7231Error::getPath($code, $allow_custom), [
			"description" => $httpResponse,
			"message"     => $message,
			"url"         => Utils::URL(),
			"title"       => $httpResponse
		]);

		exit($code);
	}
	
	public static $httpErrors = [
		"400" => "Bad Request",
		"404" => "Not Found",
		"403" => "Forbidden",
		"405" => "Method not allowed",
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
	
	public static function err405($message = NULL, $allow_custom = true) {
		self::Display(405, $message, "", $allow_custom);
	}

	public static function err500($message = NULL, $allow_custom = true) {
		self::Display(500, $message, "", $allow_custom);
	}

	public static function err429($message = NULL, $allow_custom = true) {
		self::Display(429, $message, "", $allow_custom);
	}

	public static function err400($message = NULL, $allow_custom = true) {
		self::Display(400, $message, "", $allow_custom);
	}
	#endregion
}
