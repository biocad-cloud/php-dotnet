<?php

class FileSystem {
		
	/**
	 * Copy a file, or recursively copy a folder and its contents
	 * @author      Aidan Lister <aidan@php.net>
	 * @version     1.0.1
	 * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
	 * @param       string   $source    Source path
	 * @param       string   $dest      Destination path
	 * @param       int      $permissions New folder creation permissions
	 * @return      bool     Returns true on success, false on failure
	 */
	public static function xcopy($source, $dest, $permissions = 0755) {
			
		// Check for symlinks
		if (is_link($source)) {
			return symlink(readlink($source), $dest);
		}

		// Simple copy for a file
		if (is_file($source)) {
			return copy($source, $dest);
		}

		// Make destination directory
		if (!is_dir($dest)) {
			mkdir($dest, $permissions);
		}

		// Loop through the folder
		$DIR = dir($source);
		// echo $source;
		// 目标文件夹不存在的时候会出错
		while (false !== ($entry = $DIR->read())) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}

			// Deep copy directories
			gitbook_writer::xcopy(
				"$source/$entry", 
				"$dest/$entry", 
				$permissions);
		}

		// Clean up
		$DIR->close();
			
		return true;
	}	
}

?>