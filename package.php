<?php

include_once(dotnet::GetDotnetManagerDirectory() . "/Microsoft/VisualBasic/Strings.php");

/**
 * dotnet package manager, you must include this module at first.
 * 
 * 在php之中有一个DOTNET类型：http://php.net/manual/en/class.dotnet.php
 * 但是这个模块的使用有诸多的限制，假若使用本项目的时候，发现出现错误:
 * 
 * Fatal error: Cannot redeclare class dotnet in "mod\php.NET\package.php" on line 6
 * 
 * 则应该要检查一下你的php服务器的设置是否是区分大小写的？
 * 因为这个类名称dotnet假若不区分大小写的话，是和系统自带的DOTNET类型同名的
 */
class dotnet {

    /**
     * 只需要修改这个参数的逻辑值就可以打开或者关闭调试器的输出行为
     */
    public static $system_DEBUG = False;

    /**
     * 更改调试器的输出行为
     */
    function setup_debugger() {
        if (False == dotnet::$system_DEBUG) {
            dotnet::SuppressWarningMessage();
        } else {
            dotnet::ShowAllMessage();
        }
    }

    /**
     * This method have not implemented yet!
     * 
     * Usage:
     *      die(dotnet::$MethodNotImplemented);
     */
    public static $MethodNotImplemented = "This method have not implemented yet!";

    /**
     * 经过格式化的var_dump输出
     */
    public static function VarDump($o) {

        // var_dump函数并不会返回任何数据，而是直接将结果输出到网页上面了，
        // 所以在这里为了能够显示出格式化的var_dump结果，在这里前后都
        // 添加<code>标签。
        echo "<code><pre>";
        $string = var_dump($o);    
        echo "</pre></code>";    
    }

    /**
     * 对于这个函数额调用者而言，就是获取调用者所在的脚本的文件夹位置
     * 这个函数是使用require_once来进行模块调用的
     *
     * @param mod: 直接为命名空间的路径，不需要考虑相对路径或者添加文件后缀名，例如需要导入VisualBasic的Strings模块的方法，只需要调用代码
     * 
     *     dotnet::Imports("Microsoft.VisualBasic.Strings");
     * 
     * @return string 这个函数返回所导入的模块的完整的文件路径
     * 
     */
    public static function Imports($mod) {         
        $initialFile = dotnet::GetDotnetManagerLocation();
        $DIR         = dotnet::ParentDirectory($initialFile);
        // $DIR = dotnet::ParentDirectory($DIR);

        // echo basename($mod)."<br/>";
        // echo $initialFile."<br/>";
        // 因为WithSuffixExtension这个函数会需要依赖小数点来判断文件拓展名，
        // 所以替换操作要在if判断之后进行  
        if (dotnet::WithSuffixExtension($mod, "php")) {
            $mod = str_replace(".", "/", $mod); 
            $mod = "{$DIR}/{$mod}";
        } else {
            $mod = str_replace(".", "/", $mod); 
            $mod = "{$DIR}/{$mod}.php";
        }   
                
        // echo $mod."<br/>";

        // 在这里导入需要导入的模块文件
        include_once($mod);
        // 返回所导入的文件的全路径名
        return $mod;
    }

    /**
     * 判断这个文件路径是否是以特定的文件拓展名结尾的？这个函数大小写不敏感
     */
    public static function WithSuffixExtension($path, $ext) {
        $array  = Strings::Split($path, "\.");
        $lastEl = array_values(array_slice($array, -1));
        $lastEl = $lastEl[0];

        // echo $lastEl . "<br />";
        // echo $ext;

        return Strings::LCase($lastEl) == Strings::LCase($ext);
    }

    /**
     * 获取得到package.php这个文件的文件路径
     * 
     * @return string
     */
    public static function GetDotnetManagerLocation() {     

        // 第一个栈片段就是当前的函数调用所在的文件位置，直接return获取得到这个文件位置信息
        foreach(debug_backtrace() as $k=>$v) {
            extract($v); 
            return $file;
        }
    }

    /**
     * 获取得到package.php这个文件的所处的文件夹的位置
     */
    public static function GetDotnetManagerDirectory() {
        return dotnet::ParentDirectory(
               dotnet::GetDotnetManagerLocation());
    }

    /**
     * 获取得到某一个文件的其所处的父文件夹的全路径
     */
    public static function ParentDirectory($file) {

        dotnet::SuppressWarningMessage();   

        $file = str_replace("\\", "/", $file);
        $file = split("/", $file);
        // 去除最后一个元素，最后一个元素为文件名，这样子剩下的都是文件夹的单词了 
        array_pop($file);
        $file = join("/", $file);

        dotnet::ShowAllMessage();

        return $file;
    }

    public static function ThrowException($message) {
        $stackTrace = StackTrace::GetCallStack();
		$message = $message.'<br/>'.$stackTrace;
		die($message);
    }

    /**
     * 调用这个函数之后，将会阻止继续输出警告信息，假若在调用了这个函数之后，
     * 需要重新显示错误消息，则可以调用 dotnet::ShowAllMessage 函数
     */
    public static function SuppressWarningMessage() {

        // 阻止php输出警告信息
        // http://php.net/manual/en/function.error-reporting.php

        // 不显示任何错误信息以及警告信息
        error_reporting(0);

        // 显示出警告信息
        // error_reporting(E_WARNING);
    }

    /**
     * 使用这个函数将会显示出所有的错误消息
     */ 
    public static function ShowAllMessage() {

        // Report all PHP errors (see changelog)
        error_reporting(E_ALL);
    }
}

?>