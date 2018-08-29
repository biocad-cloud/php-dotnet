<?php

Imports("System.Diagnostics.StackFrame");
Imports("System.Text.StringBuilder");
Imports("Microsoft.VisualBasic.Strings");

/**
 * Represents a stack trace, which is an ordered collection of 
 * one or more stack frames.
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
     * @var array
    */
    var $frames;

    public function __construct($backtrace = null) {
        $first  = true;
        $frames = [];

        if (!$backtrace) {
            $backtrace = debug_backtrace();
        } else {
            $first = false;
        }

        foreach($bt as $k=>$v) {
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
}

?>