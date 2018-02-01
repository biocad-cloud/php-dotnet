<?php

/**
 * Provides properties and methods for working with drives, files, and directories.
 *
 */
class FileSystem {
	
	/**
	 * Writes text to a file.
	 *
	 */
	public static void WriteAllText(string $file, string $text, bool $append) {
		if ($append) {
			file_put_contents($file, $text, FILE_APPEND);
		} else {
			file_put_contents($file, $text);
		}		
	}
		
	/**
	 * Returns the contents of a text file as a String.
	 *
	 * @param file: Name and path of the file to read.
	 */
	public static string ReadAllText(string $file) {
		$text = file_get_contents($file);
		return $text;
	}
		
	/**
	 * Renames a file.
	 *
	 * @param file:    File to be renamed.
	 * @param newName: New name of file.
	 *
	 */
	public static void RenameFile(string $file, string $newName) {
		rename($file, $newName);
	}
		
	/*
	 * Returns a collection of strings representing the path names of subdirectories within a directory.
     *
	 */
	public static function GetDirectories($directory) {
		return glob($directory . '/*', GLOB_ONLYDIR);
	}
		
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
			$a = "$source/$entry";
			$b = "$dest/$entry";
			FileSystem::xcopy($a, $b, $permissions);
		}

		// Clean up
		$DIR->close();
			
		return true;
	}	
}
?>