<?php

class StackFrame {

    /**
     * @var array
    */
    var $frame;

    /**
     * 定义当本机或 Microsoft 中间语言 (Microsoft Intermediate Language, MSIL) 
     * 偏移量未知时从 ``System.Diagnostics.StackFrame.GetNativeOffset`` 或 
     * ``System.Diagnostics.StackFrame.GetILOffset`` 方法返回的值。
     * 
     * 此字段为常数。
     * 
     * @var integer
    */
    const OFFSET_UNKNOWN = -1;

    /**
     * @param array $frame
    */
    public function __construct($frame) {
        $this->frame = $frame;
    }

    /**
     * @return string
    */
    public function GetMethod() {
        $className = Utils::ReadValue($this->frame, "class", "&lt;GlobalEnvir>");

        if (!empty($className)) {
            return "$className::{$this->frame['function']}";
        } else {
            return $this->frame["function"];
        }
    }

    public function GetILOffset() {
        return self::OFFSET_UNKNOWN;
    }

    public function GetFileName() {
        $file = $this->frame["file"];
        
        if (!APP_DEBUG) {
            # 在非调试模式下，将服务器的文件系统信息隐藏掉

            if (defined("PHP_DOTNET")) {
                $file = Strings::Replace($file, PHP_DOTNET, "/usr~/->/docker.libX64/php.NET/src/");
            } 
            if (defined("APP_PATH")) {
                $file = Strings::Replace($file, APP_PATH,   "/wwwroot/docker/ubuntu~/->/");
            }
            if (defined("SITE_PATH")) {
                $file = Strings::Replace($file, SITE_PATH,  "/wwwroot/docker/ubuntu~/->/");
            }
        } else {
            # do nothing
        }
        
        // fix for windows path
        $file = Strings::Replace($file, "\\", "/");
        $file = Strings::Replace($file, "//", "/");

        return $file;
    }

    public function GetFileLineNumber() {
        return $this->frame["line"];
    }

    public function ToString() {
        $file     = $this->GetFileName();
        $line     = $this->GetFileLineNumber();
        $function = $this->GetMethod();

        return "    at $function in $file:line $line";
    }
}
 