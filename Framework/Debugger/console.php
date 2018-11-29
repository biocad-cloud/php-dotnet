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

        # 在这里主要是为了跳过当前的函数以及
        # 上一层调用函数的堆栈信息
        foreach([2, 1] as $top) {
            if (!empty($trace = self::fixUbench($backtrace, $top))) {
                return $trace;
            }
        }

        return [
            "file" => "Invalid stack trace", 
            "line" => 0
        ];
    }
    
    /**
     * 似乎因为使用了Ubench的lambda函数之后栈的层次信息就错位了
     * 为了兼容Ubench的lambda函数，在这里跳过Ubench的栈信息
     * 
     * 在这里我们假设在Ubench模块之中永远都不会调用调试器的终端输出函数
     * 
     * @param integer $top 栈信息片段的偏移量
    */
    private static function fixUbench($backtrace, $top) {
        $i = 0;
        
        foreach($backtrace as $k => $v) {
            # 跳过这个函数的栈片段
            if ($i <= $top) {
                $i++;
            } else if (array_key_exists("class", $v) && $v["class"] === "Ubench") {
                # 因为认为在Ubench模块之中永远都不会出现调试器的代码调用
                # 所以在这里是Ubench模块的话，当前的栈信息肯定是错位的
                # 跳过这个错位的栈信息
                break;
            } else {
                # 缩短路径字符串，优化显示
                $v["file"] = self::shrinkPath($v["file"]);
                return $v;
            };
        }

        return null;
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
        } else if (IS_CLI && FRAMEWORK_DEBUG) {
            $time = Utils::Now(false);
            echo "[$time] $msg\n";
        }
    }

    /**
     * 经过格式化的``var_dump``或者``json``输出
     * 
     * @param mixed $obj 需要在终端上面输出浏览的任意php对象
    */
    public static function dump($obj, $code =2) {
        if (IS_CLI) {
            $time = Utils::Now(false);
            echo "[$time] \n";
            echo var_dump($obj);
        } else if (APP_DEBUG) {
            $trace = self::backtrace();
            self::$logs[]  = [
                "code"  => $code,
                "msg"   => self::objDump($obj, false),
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