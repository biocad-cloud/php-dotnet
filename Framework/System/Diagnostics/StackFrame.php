<?php

/** 
 * 提供关于 ``System.Diagnostics.StackFrame``（表示当前线程的调用堆栈中的一个函数调用）的信息。
*/
class StackFrame {

    /**
     * PHP之中得到的原始的栈片段信息
     * 
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
     * 获取在其中执行帧的方法。
     * 
     * @return string
    */
    public function GetMethod() {
        $className = Utils::ReadValue($this->frame, "class", "&lt;Unknown>");

        if (!empty($className)) {
            return "$className::{$this->frame['function']}";
        } else {
            return $this->frame["function"];
        }
    }

    /** 
     * 获取离开所执行方法的 Microsoft 中间语言 (Microsoft Intermediate Language, MSIL) 代码开头的偏移量。
     * 根据实时 (JIT) 编译器是否正在生成调试代码，此偏移量可能是近似量。
     * 该调试信息的生成受 ``System.Diagnostics.DebuggableAttribute`` 控制。
    */
    public function GetILOffset() {
        return self::OFFSET_UNKNOWN;
    }

    /** 
     * 获取包含所执行代码的文件名。
     * 该信息通常从可执行文件的调试符号中提取。
    */
    public function GetFileName() {
        $file = $this->frame["file"];
        
        # 在非调试模式下，将服务器的文件系统信息隐藏掉
        if (!APP_DEBUG) {
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

    /** 
     * 获取文件中包含所执行代码的行号。
     * 该信息通常从可执行文件的调试符号中提取。
    */
    public function GetFileLineNumber() {
        return $this->frame["line"];
    }

    /** 
     * 生成堆栈跟踪的可读表示形式。
    */
    public function ToString() {
        $file     = $this->GetFileName();
        $line     = $this->GetFileLineNumber();
        $function = $this->GetMethod();

        return "    at $function in $file:line $line";
    }
}
 