<?php

class Error {
	
	public static function getPath($code) {
		return dirname(__FILE__) . "/$code.html";
	}
	
	public static function err404() {
		echo file_get_contents(Error::getPath(404));
	}
	
	public static function err403() {
		echo file_get_contents(Error::getPath(403));
	}
	
	public static function err500() {
		echo file_get_contents(Error::getPath(500));
	}
}

?>