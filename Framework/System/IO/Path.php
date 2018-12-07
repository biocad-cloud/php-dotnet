<?php

/** 
 * Path helper
*/
class Path {
	
	/**
	 * Returns the file name of the specified path string without the extension.
	 *
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
	*/
	public static function GetExtensionName($filePath) {
		$array = explode('.', $filePath);
		$extension = end($array);
		return $extension;
	}
}