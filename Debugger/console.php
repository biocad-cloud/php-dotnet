<?php

/**
 * 
 * 用户调试记录器
 * 
*/
class console {

    private $logs;

    public function log($msg) {

    }

    public function writeline($s, $args = NULL) {
        
    }

    /**
     * 经过格式化的var_dump输出
     * 
     */
    public function dump($obj) {
        // var_dump函数并不会返回任何数据，而是直接将结果输出到网页上面了，
        // 所以在这里为了能够显示出格式化的var_dump结果，在这里前后都
        // 添加<code>标签。
        echo "<code><pre>";
        echo var_dump($o);    
        echo "</pre></code>";    
    }

    public function error($msg) {

    }

    public static function printCode($code) {
        echo "<code><pre>";
        echo $code;
        echo "</pre></code>";
    }
}

?>