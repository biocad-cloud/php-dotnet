<?php

/**
 * 用户调试记录器
 * 这个模块所记录的值会在调试器视图的Console页面上面显示出来
*/
class console {

    /**
     * @var array
    */
    public static $logs;

    public static function error_handler($errno, $errstr, $errfile, $errline) {
        self::$logs[] = [
            "code"  => $errno, 
            "msg"   => $errstr, 
            "file"  => self::shrinkPath($errfile), 
            "line"  => $errline, 
            "color" => "red"
        ];
    }

    private static function isDebugMode() {
        if (defined("APP_DEBUG")) {
            return APP_DEBUG;
        } else {
            return false;
        }
    }

    private static function shrinkPath($file) {
        if (strpos($file, PHP_DOTNET) === 0) {
            $file = str_replace(PHP_DOTNET, "", $file);
            $file = "[PHP_DOTNET]$file";
        } elseif (defined("APP_PATH")) {
            $file = str_replace(APP_PATH, "", $file);
            $file = "[APP_PATH]$file";
        }

        return $file;
    }

    private static function backtrace(){
        $backtrace = array_reverse(debug_backtrace());
        $i = 0;     

        foreach($backtrace as $k => $v) {
            # 跳过这个函数的栈片段
            if ($i <= 2) {
                $i++;
            } else {
                # 缩短路径字符串，优化显示        
                $v["file"] = self::shrinkPath($v["file"]);
                
                return $v;
            };
        }
        
        return ["file" => "Invalid stack trace", "line" => 0];
    }
    
    /**
     * 输出一般的调试信息，代码默认为零。表示无错误
    */
    public static function log($msg, $code = 0) {
        if (self::isDebugMode()) {
            $trace = self::backtrace();
            self::$logs[] = [
                "code"  => $code, 
                "msg"   => $msg, 
                "file"  => $trace["file"], 
                "line"  => $trace["line"], 
                "color" => "black"
            ];
        }        
    }

    /**
     * 经过格式化的var_dump输出
     * 
     */
    public static function dump($obj, $code =2) {
        if (self::isDebugMode()) {
            $trace = self::backtrace();
            $msg   = null;

            if (is_array($obj) || is_object($obj)) {
                $id   = "json" . time();
                $json = json_encode($obj);
                $msg  = "<div class='jsonview-container' id='$id'></div>
                         <script type='text/javascript'>                                
                            $(function() {
                                $('#$id').JSONView($json, { collapsed: true });
                            });
                         </script>";
            } else {
                $msg = "<pre>" . self::varDumpToString($obj) . "</pre>";
            }

            self::$logs[]  = [
                "code"  => $code,
                "msg"   => $msg,
                "file"  => $trace["file"],
                "line"  => $trace["line"], 
                "color" => "black"
            ];
        }        
    }
    
    /**
     * 返回输出缓冲区的内容
    */
    private static function varDumpToString($var) {
        ob_start();
        var_dump($var);
        $result = ob_get_clean();
        return $result;
    }

    public static function error($msg, $code = 1) {
        if(self::isDebugMode()){
            $trace = self::backtrace();
            self::$logs[] = [
                "code"  => $code, 
                "msg"   => "<span style='color:red'>" .  $msg . "</span>", 
                "file"  => $trace["file"], 
                "line"  => $trace["line"], 
                "color" => "red"
            ];
        }
    }

    public static function printCode($code) {
        if(self::isDebugMode()){
            $trace = self::backtrace();
            self::$logs[] = [
                "code"  => 0, 
                "msg"   => "<pre>$code</pre>", 
                "file"  => $trace["file"], 
                "line"  => $trace["line"], 
                "color" => "black"
            ];
        }
    }
}

?>