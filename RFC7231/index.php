<?php

Imports("php.Utils");

/**
 * Custom error page supports
 */
class RFC7231Error {
	
	public static function getPath($code) {
		return (DotNetRegistry::RFC7231Folder() ?? dirname(__FILE__)) . "/$code.html";
	}
	
	private static function display($code, $message) {
		header($httpResponse = RFC7231Error::getRFC($code));		
		View::Show(RFC7231Error::getPath($code), [
			"message" => $message,
			"url"     => Utils::URL(),
			"title"   => $httpResponse
		]);
		exit(-1);
	}
	
	private static function getRFC($code) {
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
		}
	}
	
	public static function err404($message = NULL) {
		self::display(404, $message);
	}
	
	public static function err403($message = NULL) {
		self::display(403, $message);
	}
	
	public static function err500($message = NULL) {
		self::display(500, $message);
	}
}

?>
