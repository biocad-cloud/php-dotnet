<?php

/**
 * Represents a stack trace, which is an ordered collection of one or more stack frames.
 */
class StackTrace {

    // public void AddLog(string message, [CallerMemberName] string memberName = "", 
    // [CallerFilePath] string sourceFilePath = "", [CallerLineNumber] int sourceLineNumber = 0)
    // {
    //     StringBuilder sb = new StringBuilder();
    //     sb.AppendLine("Message: " + message);
    //     sb.AppendLine("Member/Function name: " + memberName);
    //     sb.AppendLine("Source file path: " + sourceFilePath);
    //     sb.AppendLine("Source line number: " + sourceLineNumber);
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

    }

}

?>