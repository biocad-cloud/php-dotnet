<?php 

// visualbasic之中的一些简单的数据类型转换关键词
class Conversion {

    public static function CInt($str) {
        return intval($str);
    }

    public static function CDbl($str) {
        return doubleval($str);
    }

    public static function CBool($str) {
        return boolval($str);
    }

    public static function CStr($str) {
        return (string) $str;
    }

    public static function CSng($str) {
        return floatval($str);
    }

}

?>