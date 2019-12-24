<?php

imports("System.Diagnostics.StackFrame");
imports("System.Text.StringBuilder");
imports("Microsoft.VisualBasic.Strings");

/**
 * Represents a stack trace, which is an ordered collection of 
 * one or more stack frames.
 * 
 * 表示堆栈跟踪，这是一个或多个堆栈帧的有序的集合。
*/
class StackTrace {

    // public void AddLog(string message, 
    //                    [CallerMemberName] string memberName = "", 
    //                    [CallerFilePath] string sourceFilePath = "", 
    //                    [CallerLineNumber] int sourceLineNumber = 0) {
    //     StringBuilder sb = new StringBuilder();
    //     sb.AppendLine("Message: " + message);
    //     sb.AppendLine("Member/Function name: " + memberName);
    //     sb.AppendLine("Source file path: " + sourceFilePath);
    //     sb.AppendLine("Source line number: " + sourceLineNumber);
    //     
    //     //Create log file
    //     string FileName = @"D:\" + DateTime.Now.ToString("yyyyMMddhhmmss") + ".log";
    //     if (File.Exists(FileName)) {
    //         File.Delete(FileName); // remove existing
    //     }
    //     using (StreamWriter sw = File.CreateText(FileName)) {
    //         sw.Write(sb.ToString());         // write entire contents
    //         sw.Close();
    //     }
    // }

    /**
     * @var StackFrame[]
    */
    var $frames;

    /**
     * 获取堆栈跟踪中的帧数。
     *
     * @return integer 堆栈跟踪中的帧数。
    */
    public function FrameCount() {
        if (empty($this->frames)) {
            return 0;
        } else {
            return count($this->frames);
        }
    }

    /**
     * 假若这个构造函数传递了初始参数，在这个构造函数之中会自动跳过第一个栈信息
     * 所以不需要对传递进来的信息进行额外的处理
     * 
     * @param array $backtrace
    */
    public function __construct($backtrace = null) {
        $first  = true;
        $frames = [];

        if (!$backtrace) {
            $backtrace = debug_backtrace();
        } else {
            $first = false;
        }

        foreach($backtrace as $k => $v) {
            if ($first) {
                # 第一个栈片段信息是当前的这个函数
                # 跳过
                $first = false;
                continue;
            } else {
                array_push($frames, new StackFrame($v));
            }
        }

        $this->frames = $frames;
    }

    /** 
     * 获取得到某一个栈片段的信息
     * 
     * @return StackFrame
    */
    public function GetFrame($index) {
        return $this->frames[$index];
    }

    /**
     * 获取当前的函数调用堆栈
     * 
     * @return StackTrace
    */
    public static function GetCallStack() {
        return new StackTrace(debug_backtrace());
    }

    /**
     * Get caller file path
     * 
     * @return string
    */
    public static function GetCallerFile() {
        $caller = False;

        foreach(debug_backtrace() as $k => $v) {
            if ($caller) {
                // 这里是第二个栈片段 
                return $v["file"];
            } else {
                // 第一个栈片段是GetCallerFile这个函数，所以跳过
                // 第二个站片段才是我们所需要的Caller所在的文件路径
                $caller = True;
            }
        }
    }
    
    /**
     * 得到调用当前所执行的函数的调用者的函数名称
     * 
     * @return string Returns the caller function name
    */
	public static function GetCallerMethodName() {
		// 得到了当前函数的调用函数堆栈
		// 1. current -> GetCallerMethodName()
		// 2. caller -> current -> GetCallerMethodName()
		$trace  = debug_backtrace();
		$caller = $trace[2];
		
		return $caller['function'];
    }
    
    /**
     * 格式化输出栈追踪信息为字符串
     * 
     * @param boolean $html
     * 
     * @return string 经过格式化之后的栈追踪信息字符串
    */
    public function ToString($html = true) {
        $newLine = $html ? "<br />" . PHP_EOL : PHP_EOL;
        $trace   = new StringBuilder("", $newLine);

        if ($html) {
            $trace->Append("<code>");
        }
        foreach($this->frames as $frame) {
            $trace->AppendLine($frame->ToString());
        }
        if ($html) {
            $trace->Append("</code>");
        }

        return $trace->ToString();
    }
}