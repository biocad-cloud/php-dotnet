<?php

class Path {
	
	/*
	 * Returns the file name of the specified path string without the extension.
	 *
	 */
	public static function GetFileNameWithoutExtension($filePath) {
		$path_parts = pathinfo($filePath);
		return $path_parts['filename'];
	}
}

?>