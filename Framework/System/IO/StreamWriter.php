<?php

imports("System.IDisposable");

/**
 * Implements a System.IO.TextWriter for writing characters to a stream in a particular encoding.
 * To browse the .NET Framework source code for this type, see the Reference Source.
*/
class StreamWriter implements \System\IDisposable {

    /**
     * 文件句柄
     * 
     * @var resource
    */
    var $file;
    /**
     * @var string
    */
    var $path;

    /**
     * Initializes a new instance of the System.IO.StreamWriter class for the specified file 
     * by using the default encoding and buffer size. If the file exists, it can be either 
     * overwritten or appended to. If the file does not exist, this constructor creates a 
     * new file.
    */
    public function __construct($path, $append = false) {
        if ($append) {
            $this->file = fopen($path, "a");
        } else {
            $this->file = fopen($path, "w");
        }       

        $this->path = realpath($path);
    }

    /**
     * @return StreamWriter
    */
    public function Write($x) {
        fwrite($this->file, $x);
        return $this;
    }

    /**
     * @return StreamWriter
    */
    public function WriteLine($x) {
        fwrite($this->file, $x . "\n");
        return $this;
    }

    public function Close() {
        fclose($this->file);
    }

    public function Dispose() {
        fclose($this->file);
    }
}