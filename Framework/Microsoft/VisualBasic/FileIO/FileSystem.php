<?php

dotnet::Imports("Microsoft.VisualBasic.Strings");
dotnet::Imports("System.Text.RegularExpressions.Regex");

/**
 * Provides properties and methods for working with drives, 
 * files, and directories in VisualBasic.
*/
class FileSystem {
	
	/** 
	 * Get file name from a given file path and without extensions.
	 * 
	 * @param string $path The given file path string value.
	*/
	public static function BaseName($path) {
		$path = explode("/", $path);
		$path = $path[count($path) - 1];
		$path = explode("\\", $path);

		$fileName = $path[count($path) - 1];
		$fileName = explode(".", $fileName);
		$tokens   = [];

		if (count($fileName) == 1) {
			return $fileName;
		}

		for($i = 0; $i < count($fileName) - 1; $i++) {
			array_push($tokens, $fileName[$i]);
		}

		return implode(".", $tokens);
	}

	/**
	 * 将所给定的文件路径之中的多于重复的分隔符给消除掉，可能会解决一些对路径格式
	 * 要求比较严格的程序的BUG有帮助
	 * 
	 * @param boolean $windowsStyle If true, then all of the ``/`` will be replaced as ``\`` 
	 * @param string $path
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
	 * 函数返回一个函数指针，使用``!wq``指令来关闭文件
	 * 
	 * @return callable 只接受一个文件数据的输入参数
	*/
	public static function OpenWriter($file, $appendNewLine = FALSE, $append = FALSE) {
		$mode   = $append ? "a" : "w";
		$handle = fopen($file, $mode);

		return function($text) use ($handle, $appendNewLine) {
			if ($text === "!wq") {
				fclose($handle);
			} else {
				fwrite($handle, $text);

				if ($appendNewLine) {
					fwrite($handle, "\n");
				}
			}
		};
	}

	/**
	 * Writes text to a file.
	 * 
	 * 这个函数会自动创建文件夹
	 *
	 * @param string $file 文件路径
	 * @param string $text 文件内容字符串
	 * @param boolean $append 是否在原有的文件基础之上进行数据的追加？默认不是，会覆盖掉源文件
	 * 
	 * @return void This function returns nothing.
	*/
	public static function WriteAllText($file, $text, $append = FALSE) {
		if (!file_exists($file)) {
			$dir = dirname($file);

			if (!file_exists($dir)) {
				mkdir($dir, 0777, true);
			}
		}

		if ($append) {
			return file_put_contents($file, $text, FILE_APPEND);
		} else {
			return file_put_contents($file, $text);
		}
	}
		
	/**
	 * Returns the contents of a text file as a String.
	 *
	 * 如果目标文件不存在的话，函数会直接返回空值
	 * 
	 * @param string $file Name and path of the file to read.
	 * 
	 * @return string 
	*/
	public static function ReadAllText($file, $default = null) {
		if (!file_exists($file)) {
			return $default;
		} else {
			return file_get_contents($file);
		}
	}

	public static function ReadFirstLine($path) {
		foreach(self::IteratesAllLines($path) as $line) {
			return $line;
		}
	}

	/**
	 * 这是一个迭代器函数，只能够配合foreach一起使用
	*/
	public static function IteratesAllLines($path) {
		$handle = fopen($path, "r");

		if ($handle) {
			while (($line = fgets($handle)) !== false) {
				# 右边肯定会存在一个\r或者\n换行符，在这里将其删除
				yield rtrim($line, "\r\n");
			}
		
			fclose($handle);
		} else {
			// error opening the file.
		} 
	}

	/**
	 * Renames a file.
	 *
	 * @param string $file:    File to be renamed.
	 * @param string $newName: New name of file.
	 *
	*/
	public static function RenameFile($file, $newName) {
		rename($file, $newName);
	}
	
	private static function DisplayFilePermissions($Mode) {

		# Determine Type
		if ($Mode & 0x1000) {			
			$Type = 'p'; // FIFO pipe
		} else if ($Mode & 0x2000) {
			$Type = 'c'; // Character special
		} else if ($Mode & 0x4000) {
			$Type = 'd'; // Directory
		} else if ($Mode & 0x6000) {
			$Type = 'b'; // Block special
		} else if ($Mode & 0x8000) { 
			$Type = '-'; // Regular 
		} else if ($Mode & 0xA000) {
			$Type = 'l'; // Symbolic Link 
		} else if ($Mode & 0xC000) {
			$Type = 's'; // Socket
		} else {
			$Type = 'u';// UNKNOWN
		}
		 
		# Determine permissions		 
		$Owner['read']    = ($Mode & 00400) ? 'r' : '-';     		 
		$Owner['write']   = ($Mode & 00200) ? 'w' : '-';     		 
		$Owner['execute'] = ($Mode & 00100) ? 'x' : '-';     		 
		$Group['read']    = ($Mode & 00040) ? 'r' : '-';     		 
		$Group['write']   = ($Mode & 00020) ? 'w' : '-';     		 
		$Group['execute'] = ($Mode & 00010) ? 'x' : '-';     		 
		$World['read']    = ($Mode & 00004) ? 'r' : '-';     		 
		$World['write']   = ($Mode & 00002) ? 'w' : '-';     		 
		$World['execute'] = ($Mode & 00001) ? 'x' : '-';      
		 
		# Adjust for SUID, SGID and sticky bit     		 
		if ($Mode & 0x800) $Owner['execute'] = ($Owner['execute'] == 'x') ? 's' : 'S';   
		if ($Mode & 0x400) $Group['execute'] = ($Group['execute'] == 'x') ? 's' : 'S';   
		if ($Mode & 0x200) $World['execute'] = ($World['execute'] == 'x') ? 't' : 'T'; 
		 
		return $Type . $Owner['read'] . $Owner['write'] . $Owner['execute'] 
					 . $Group['read'] . $Group['write'] . $Group['execute'] 
					 . $World['read'] . $World['write'] . $World['execute']; 
		 
	}  

	/** 
	 * @return boolean|null 文件不存在的时候返回空值
	*/
	public static function ViewPermission($path) {
		if (file_exists($path)) {
			return self::DisplayFilePermissions(fileperms($path));
		} else {
			return null;
		}
	}

	# 2018-12-28 如果查看权限发现没有问题，但是任然无法读取文件夹或者文件，则很有可能是SELinux在阻止php对文件的访问
	# 这个时候会需要在SELinux里面设置额外的访问权限规则或者将SELinux的等级从Enforcing调整至Permissive
	# 
	# 获取SELinux的状态getenforce
	# 设置SELinux的状态setenforce 0或者1
	#
	# https://blog.lysender.com/2015/07/centos-7-selinux-php-apache-cannot-writeaccess-file-no-matter-what/

	/**
	 * Returns a collection of strings representing the path names of subdirectories 
	 * within a directory.
     *
	 * @param string $directory 目标文件夹的文件路径
	 * 
	 * @return string[]
	*/
	public static function GetDirectories($directory, $returnRealpath = false) {
		if (file_exists($directory)) {
			
			if ($returnRealpath) {
				$directory = realpath($directory);
			}
			
			$directory = rtrim($directory, "/");
			$list      = [];
			
			// Loop through the folder
			$DIR = dir($directory . "/");

			if ($DIR === false || empty($DIR)) {
				$permission = self::ViewPermission($directory . "/");
				$msg = "[$permission $directory/] have no permission to read!";
				$msg = $msg . " If SELinux has enable, please add access rule for php server or set SELinux to [Permissive].";

				if (!posix_access($directory, POSIX_R_OK)) {
					$error = posix_get_last_error();				
					$msg   = $msg . " Error $error: " . posix_strerror($error);
				}

				throw new dotnetException($msg);
			}

			while (false !== ($entry = $DIR->read())) {
				// Skip pointers
				if ($entry == '.' || $entry == '..') {
					continue;
				} else {
					$file = "$directory/$entry";

					if (is_dir($file)) {
						array_push($list, $file);
					}
				}
			}

			$DIR->close();

			return $list;

		} else {
			console::warn("Directory '$directory' is not exists on the filesystem!");
			return [];
		}
	}
		
	public static function GetParentPath($path) {
		return dirname($path);
	}

	/**
	 * Creates a directory. 
	 * (使用这个函数会自动判断文件夹是否存在，主要是为了不出现php的warning而编写的)
	 *
	 * @param string $directory Name and location of the directory.
	*/
	public static function CreateDirectory($directory) {
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
	 * @param string $directory Name and location of the directory.
	 * @param string $suffix The file extension name. By default is get all files in target directory.
	 *               (*.ext或者ext这两种格式都允许，默认是*.*，即不做任何筛选)
	 * 
	 * @return string[] 文件的完整路径集合
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
	 * （使用这个函数会递归的将文件夹之中的所有文件都从source文件夹拷贝到dest文件夹） 
	 * 
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
			FileSystem::XCopy($a, $b, $permissions);
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

	/**
	 * 对bytes数值进行格式自动优化显示
	 * 
	 * @param integer $bytes 
	 * 
	 * @return string 经过自动格式优化过后的大小显示字符串
	*/
	public static function Lanudry($bytes) {
		$symbols = ["B", "KB", "MB", "GB", "TB"];
		$exp     = floor( log($bytes) / log(1000) );
		$symbol  = $symbols[$exp];
		$val     = ($bytes / pow(1000, floor($exp)));
		
		return sprintf("%.2f $symbol", $val);		
	}
}
?>