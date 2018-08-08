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

    private static function backtrace(){
        $backtrace = array_reverse(debug_backtrace());
        $i = 0;     

        foreach($backtrace as $k => $v){
            # 跳过这个函数的栈片段
            if ($i <= 2) {
               $i++;
            } else {
              return $v;
           };

        }
        
        return ["file" => "Invalid stack trace", "line" => 0];
    }

    /**
     * 输出一般的调试信息，代码默认为零。表示无错误
    */
    public static function log($msg, $code = 0) {
        $trace = self::backtrace();
        self::$logs[] = ["code" => $code, "msg" => $msg, "file" => $trace["file"], "line" => $trace["line"]];
    }

    /**
     * 经过格式化的var_dump输出
     * 
     */
    public function dump($obj) {
    
    }

    public function error($msg, $code = 1) {
        $trace = self::backtrace();
        self::$logs[] = ["code" => $code, "msg" => "<span style='color:red'>" .  $msg . "</span>", "file" => $trace["file"], "line" => $trace["line"]];
    }

    public static function printCode($code) {
        echo "<code><pre>";
        echo $code;
        echo "</pre></code>";
    }
}

?>