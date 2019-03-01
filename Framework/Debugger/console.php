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

    /**
     * 在这个函数之中显示以及处理php的警告消息
    */
    public static function error_handler($errno, $errstr, $errfile, $errline) {
        if (IS_CLI) {
            $time = Utils::Now(false);
            echo "[$time, ERROR::$errno] $errstr\n";
            echo "    at $errfile line $errline\n";
        } else {
            self::$logs[] = [
                "code"  => $errno, 
                "msg"   => Strings::Len($errstr) > 128 ? substr($errstr, 0, 128) . "..." : $errstr, 
                "file"  => self::shrinkPath($errfile), 
                "line"  => $errline, 
                "color" => "red",
                "time"  => Utils::Now(false)
            ];
        }
    }

    /**
     * 将php文件的路径进行相对简写，优化显示
    */
    private static function shrinkPath($file) {
        if (strpos($file, PHP_DOTNET) === 0) {
            $file = str_replace(PHP_DOTNET, "", $file);
            $file = "<code>[PHP_DOTNET]</code>$file";
        } elseif (defined("APP_PATH")) {
            $file = str_replace(APP_PATH, "", $file);
            $file = "<code>[APP_PATH]</code>$file";
        }

        return $file;
    }

    /**
     * Get stack backtrace
    */
    private static function backtrace(){
        $backtrace = array_reverse(debug_backtrace());
        // count - 1 为console
        // count - 2 才是上一层消息发生的位置
        $index     = count($backtrace) - 2;

        if ($index < 0) {
            return [
                "file" => "Invalid stack trace", 
                "line" => 0
            ];
        }

        $backtrace = $backtrace[$index]; 
        # 缩短路径字符串，优化显示
        $backtrace["file"] = self::shrinkPath($backtrace["file"]);
        
        return $backtrace;
    }
    
    /**
     * 输出一般的调试信息，代码默认为零。表示无错误
     * 
     * + 如果是web服务器环境下，日志信息会被缓存然后统一输出到页面的调试器窗口之中
     * + 如果是在命令行环境下，日志则会在自动添加了时间戳之后直接被打印出来
     * 
     * @param string $msg 日志消息文本
     * @param integer $code 错误代码，默认是零，表示没有错误
    */
    public static function log($msg, $code = 0) {
        if ((!IS_CLI) && APP_DEBUG) {
            $trace = self::backtrace();

            self::$logs[] = [
                "code"  => $code, 
                "msg"   => $msg, 
                "file"  => $trace["file"], 
                "line"  => $trace["line"], 
                "color" => "black",
                "time"  => Utils::Now(false)
            ];
        } else if (IS_CLI) {
            $time = Utils::Now(false);
            echo "[$time] $msg\n";
        }
    }

    /**
     * 经过格式化的``var_dump``或者``json``输出
     * 
     * @param mixed $obj 需要在终端上面输出浏览的任意php对象
     * @param string $message 这个是为了在更好的让开发人员理解对象含义的帮助信息
    */
    public static function dump($obj, $message = "PHP object value dump:", $code = 2) {
        if (IS_CLI) {
            $time = Utils::Now(false);
            echo "[$time] $message\n";
            echo var_dump($obj);
        } else if (APP_DEBUG) {
            $trace = self::backtrace();
            self::$logs[]  = [
                "code"  => $code,
                "msg"   => $message . "<br />" . self::objDump($obj, false),
                "file"  => $trace["file"],
                "line"  => $trace["line"], 
                "color" => "black",
                "time"  => Utils::Now(false)
            ];
        }        
    }
    
    /**
     * @param boolean $var_dump 是进行var_dump输出还是普通的字符串输出？
    */
    public static function objDump($obj, $var_dump = true) {
        if (is_array($obj) || is_object($obj)) {
            $id   = "json" . Utils::RandomASCIIString(6);
            $json = json_encode($obj);
            
            return "<div class='jsonview-container' id='$id'></div>
                    <script type='text/javascript'>
                        $(function() {
                            $('#$id').JSONView($json, { collapsed: true });
                        });
                    </script>";

        } else if ($var_dump) {
            $dump = self::varDumpToString($obj);
            
            return "<pre style='font-weight: bolder;font-size: 16px;padding: 0px;background-color: #fff;border: none;'>
                        $dump
                    </pre>";
        } else {
            return strval($obj);
        }
    }

    /**
     * 返回输出缓冲区的内容
    */
    public static function varDumpToString($var) {
        ob_start();
        var_dump($var);
        $result = ob_get_clean();
        return $result;
    }

    /** 
     * 打印出错误消息
    */
    public static function error($msg, $code = 1) {  
        if ((!IS_CLI) && APP_DEBUG) {
            $trace = self::backtrace();
            self::$logs[] = [
                "code"  => $code, 
                "msg"   => "<span style='color:red'>" .  $msg . "</span>", 
                "file"  => $trace["file"], 
                "line"  => $trace["line"], 
                "color" => "red",
                "time"  => Utils::Now(false)
            ];
        } else if (IS_CLI && FRAMEWORK_DEBUG) {
            $time  = Utils::Now(false);
            $trace = StackTrace::GetCallStack();

            echo "[$time, ERROR::$code] $msg\n";
            echo $trace->ToString($html = false) . "\n";
        } 
    }

    public static function warn($msg, $code = 1) {
        if ((!IS_CLI) && APP_DEBUG) {
            $trace = self::backtrace();
            self::$logs[] = [
                "code"  => $code, 
                "msg"   => "<span style='color:#cc9900'>$msg</span>", 
                "file"  => $trace["file"], 
                "line"  => $trace["line"], 
                "color" => "#cc9900",
                "time"  => Utils::Now(false)
            ];
        } else if (IS_CLI && FRAMEWORK_DEBUG) {
            $time  = Utils::Now(false);
            $trace = StackTrace::GetCallStack();

            echo "[$time, WARN::$code] $msg\n";
            echo $trace->ToString($html = false) . "\n";
        } 
    }

    public static function printCode($code) {
        if (APP_DEBUG) {
            $trace = self::backtrace();
            self::$logs[] = [
                "code"  => 0, 
                "msg"   => "<pre style='font-weight: bolder;font-size: 16px;padding: 0px;background-color: #fff;border: none;'>$code</pre>", 
                "file"  => $trace["file"], 
                "line"  => $trace["line"], 
                "color" => "black",
                "time"  => Utils::Now(false)
            ];
        }
    }
}

?>