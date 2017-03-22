<?php

class Conversion {

    /**
     * 这个函数安全的将字符串转换为数值类型  
     * 
     * @exp: 任何一非数字开始的字符串都会被解析为0
     */
    public static function Val($exp) {
    
        preg_match("^\s*-?\d+(\.\d+)?", $exp, $matches, PREG_OFFSET_CAPTURE, 3);

        $exp = $match[0];
        $exp = trim ($exp);
        $value = doubleval($exp);

        return $value;
    }
}

?>
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