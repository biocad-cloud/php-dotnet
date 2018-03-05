<?php

class MySqlExtensions {

    /**
     * 返回符合MySql所要求的格式的当前时间的字符串值
     */
    public static function Now() {        
        return date('Y-m-d H:i:s', time());
    }
}
?>