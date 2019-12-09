<?php

imports("System.IDisposable");

/**
 * Provides static methods for the creation, copying, deletion, moving, and opening of a single file, 
 * and aids in the creation of System.IO.FileStream objects.To browse the .NET Framework source code 
 * for this type, see the Reference Source.
 */
class File {

	public static function GetType($code) {
		switch($code) {
            case 7790:   return 'exe';
            break;        
            case 7784:   return 'midi'; 
            break;        
            case 8297:   return 'rar'; 
            break;        
            case 255216: return 'jpg';
            break;        
            case 7173:   return 'gif';
            break;        
            case 6677:   return 'bmp';
            break;        
            case 13780:  return 'png';
            break;        
            default:     return 'unknown';    
        }    
	}

	public static function WriteAllText($path, $contents, $encoding = "Utf8") {
		
	}	
	
	/**
	 * Opens a file, reads all lines of the file with the specified encoding, and then closes the file.
	 * 
	 * @param path      The file to open for reading.
	 * @param encoding  The encoding applied to the contents of the file.
	 *
	 * @returns A string containing all lines of the file.
	 */
	public static function ReadAllText($path, $encoding = "Utf8") {
		return file_get_contents($path);
	}

	/**
	 * @param string $path The file path
	 * @param string $encoding The text file encoding name.
	 * 
	 * @return string[]
	*/
	public static function ReadAllLines($path, $encoding = "Utf8") {
		return StringHelpers::LineTokens(file_get_contents($path));
	}

	public static function Exists($path) {
		return file_exists($path);
	}
}

class FileStream implements System\IDisposable {

	/**
	 * The file stream object itself
	 * 
	 * @var resource 
	*/
	private $handle;
	/**
	 * The file path
	 * 
	 * @var string
	*/
	private $path;

	/**
	 * Create a new file wrapper object
	 * 
	 * @param string $path The file path
	 * @param string $mode The file open mode
	*/
	public function __construct($path, $mode) {
		$this->path   = $path;
		$this->handle = fopen($path, $mode);
	}

	/**
	 * Get file length
	 * 
	 * @return integer
	*/
	public function Length() {
		return filesize($this->path);
	}

	/**
	 * The file reader position
	 * 
	 * @return integer
	*/
	public function Position() {
		return ftell($this->handle);
	}

	public function Read($length) {
		return fread($this->handle, $length);
	}

	/**
	 * Sets the current position of this stream to the given value.
	 * 
	 * @param integer $offset The point relative to origin from which to begin seeking.
	 * @param integer $origin This parameter should be use one of the 
	 * integer value as description below:
	 * 
	 * | name  |i32| description                                   |
	 * |-------|---|-----------------------------------------------|
	 * |Begin  | 0 |Specifies the beginning of a stream.           |
	 * |Current| 1 |Specifies the current position within a stream.|
     * |End    | 2 |Specifies the end of a stream.                 |
	 * 
	 * @return integer The new position in the stream.
	*/
	public function Seek($offset, $origin = 0) {
		if ($origin == 0) {
			fseek($this->handle, $offset, SEEK_SET);
		} else if ($origin == 1) {
			fseek($this->handle, $offset, SEEK_CUR);
		} else if ($origin == 2) {
			fseek($this->handle, $offset, SEEK_END);
		} else {
			return -1;
		}

		return ftell($this->handle);
	}

	/**
	 * Close the file
	*/
	public function Dispose() {
		fclose($this->handle);
	}

	public function __toString() {
		return $this->path;
	}

	/** 
	 * @return FileStream
	*/
	public static function OpenReadOnly($path) {
		return new FileStream($path, "r");
	}
}