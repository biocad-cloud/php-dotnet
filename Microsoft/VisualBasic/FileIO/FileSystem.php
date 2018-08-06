<?php

dotnet::Imports("Microsoft.VisualBasic.Strings");
dotnet::Imports("System.Text.RegularExpressions.Regex");

/**
 * Provides properties and methods for working with drives, 
 * files, and directories.
*/
class FileSystem {
	
	/**
	 * 
	 * @param windowsStyle: If true, then all of the ``/`` will be replaced as ``\`` 
	 * 
	*/
	public static function NormalizePath($path, $windowsStyle = false) {
		$path = Strings::Replace($path, '\\', "/");
		$path = Regex::Replace($path, "[/]+", "/");

		if ($windowsStyle) {
			$path = Strings::Replace($path, "/", "\\");
		}

		return $path;
	}

	/**
	 * Writes text to a file.
	 *
	 */
	public static function WriteAllText($file, $text, $append = FALSE) {
		$dir = dirname($file);

		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}

		if ($append) {
			// echo ">>>>> append " . "\n";
			// echo $text;
			// echo ">>>>> to $file" . "\n";
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
	public static function ReadAllText($file) {
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
	public static function RenameFile($file, $newName) {
		rename($file, $newName);
	}
		
	/*
	 * Returns a collection of strings representing the path names of subdirectories within a directory.
     *
	 */
	public static function GetDirectories($directory) {
		return glob($directory . '/*', GLOB_ONLYDIR);
	}
		
	public static function GetParentPath($path) {
		return dirname($path);
	}

	/**
	 * Creates a directory.
	 *
	 * @param directory Name and location of the directory.
	 * 
	 */
	public static function CreateDirectory($directory) {
		# echo realpath($directory) . "\n";
		# echo file_exists(realpath($directory)) . "\n";

		if (!file_exists($directory)) {
			return mkdir($directory, 0755, true);
		} else {
			return TRUE;
		}		
	}

	// FileSystem.GetFiles(String) As System.Collections.ObjectModel.ReadOnlyCollection(Of String)
	
	/**
	 * Returns a read-only collection of strings representing the names of files within a directory.
	 * 
	 * @param directory Name and location of the directory.
	 * @param suffix The file extension name. By default is get all files in target directory.
	 */
	public static function GetFiles($directory, $suffix = "*") {
		$list  = array_diff(scandir($directory), array('.', '..'));
		$files = array();
		$requireFilter = !$suffix || $suffix == "*" || $suffix == "*.*";
		$requireFilter = !$requireFilter;
		
		if ($requireFilter) {
			$suffix = explode(".", $suffix);
			$suffix = Strings::LCase(end($suffix));
		}
		
		foreach ($list as $i => $entry) {
			if (!is_dir($entry)) {
				
				if ($requireFilter) {
					$ext = pathinfo($entry, PATHINFO_EXTENSION);
					
					if (Strings::LCase($ext) == $suffix) {
						# hit
						array_push($files, realpath("$directory/$entry"));
					}
				} else {
					# 不需要做筛选，直接添加
					array_push($files, realpath("$directory/$entry"));
				}				
			}
		}
		
		return $files;
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
	public static function XCopy($source, $dest, $permissions = 0755) {
			
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

	/**
	 * Get temp file path.
	 * 
	 * @return string The temp file path.
	*/
	public static function TempFileName($prefix = "tmp") {
		$tmp = tempnam(sys_get_temp_dir(), $prefix);

		if ($tmp == false) {
			# 没有权限在临时文件夹之中创建？？
			$tmp_dir = "./data/temp";
			$tmp     = "$tmp_dir/$prefix" . "_" . Utils::RandomASCIIString(8);

			if (!file_exists($tmp_dir)) {
				mkdir($tmp_dir, 0777, true);
			}

			file_put_contents($tmp, "");
		}
		
		return $tmp;
	}

	public static function Lanudry($bytes) {
		if ($bytes <= 500) {
			return "$bytes B";
		} else {
			$symbols = ["B", "KB", "MB", "GB", "TB"];
			$exp = floor( log($bytes) / log(1000) );
			return sprintf('%.2f '.$symbols[$exp], ($bytes/pow(1000, floor($exp))));
		}
	}
}
?>