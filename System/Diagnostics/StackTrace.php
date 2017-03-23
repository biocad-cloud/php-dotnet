<?php


function stack2() {
echo StackTrace::GetCallStack();
}

stack2();

/**
 * Represents a stack trace, which is an ordered collection of one or more stack frames.
 */
class StackTrace {

    // public void AddLog(string message, 
    //                    [CallerMemberName] string memberName = "", 
    //                    [CallerFilePath] string sourceFilePath = "", 
    //                    [CallerLineNumber] int sourceLineNumber = 0)
    // {
    //     StringBuilder sb = new StringBuilder();
    //     sb.AppendLine("Message: " + message);
    //     sb.AppendLine("Member/Function name: " + memberName);
    //     sb.AppendLine("Source file path: " + sourceFilePath);
    //     sb.AppendLine("Source line number: " + sourceLineNumber);
    //     
    //     //Create log file
    //     string FileName = @"D:\" + DateTime.Now.ToString("yyyyMMddhhmmss") + ".log";
    //     if (File.Exists(FileName))
    //     {
    //         File.Delete(FileName); // remove existing
    //     }
    //     using (StreamWriter sw = File.CreateText(FileName))
    //     {
    //         sw.Write(sb.ToString());         // write entire contents
    //         sw.Close();
    //     }  
    // } 


    /**
     * 获取当前的函数调用堆栈
     * 
     * @return void
     */
    public static function GetCallStack() {
  
        $bt=debug_backtrace(); 
        $trace="<code>"; 
    
        foreach($bt as $k=>$v) { 
            // 解析出当前的栈片段信息
            extract($v); 
            $trace.="    at $function in $file:line $line<br/>";        
        } 
     
        $trace.="    --- End of inner exception stack trace ---<br/>";
        $trace.="</code>";

        return $trace;
    }
}

?>