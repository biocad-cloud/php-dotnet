<?php

/** 
 * Path helper
*/
class Path {
	
	/**
	 * Returns the file name of the specified path string without the extension.
	 *
	 * @param string $filePath The given file path string.
	*/
	public static function GetFileNameWithoutExtension($filePath) {
		$path_parts = pathinfo($filePath);
		return $path_parts['filename'];
	}

	public static function GetParentPath($filePath) {
		return dirname($filePath);
	}

	/** 
	 * Get extension name from a given file path
	 * 
	 * @param string $filePath The given file path.
	 * 
	 * @return string The file extension name. (返回来的拓展名是不带小数点的)
	*/
	public static function GetExtensionName($filePath) {
		$array = explode("/", str_replace("\\", "/", $filePath));
		$array = explode('.', end($array));

		if (count($array) == 1) {
			return "";
		} else {
			$extension = end($array);
			return $extension;
		}
	}
}