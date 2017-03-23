<?php 

dotnet::Imports("Microsoft.VisualBasic.Strings");

/**
 * 模拟VisualBasic之中的一些简单的数据类型转换关键词
 */ 
class Conversion {

    /**
     * 这个函数安全的将字符串转换为数值类型  
     * 
     * @exp: 任何一非数字开始的字符串都会被解析为0
     */
    public static function Val($exp) {
    
        // 首先解析出Double格式的字符串，这个解析出来的字符串前面可以包含有空格部分
        // 空格部分会在计算之前被trim去除掉
        preg_match("^\s*-?\d+(\.\d+)?", $exp, $matches, PREG_OFFSET_CAPTURE, 3);

        $exp = $match[0];
        $exp = trim($exp);
        $value = Conversion::CDbl($exp);

        return $value;
    }

    public static function CInt($str) {
        return intval($str);
    }

    /**
     * 非安全的将字符串转换为Double类型的实数，可能会因为字符串的格式问题而出错
     */
    public static function CDbl($str) {
        return doubleval($str);
    }

    /**
     * 枚举所有表示True含义的字符串
     */
    private static $TRUEs  = array("t", "y", "true", "yes", "ok", "success", "right");
    /**
     * 枚举所有表示False含义的字符串
     */
    private static $FALSEs = array("f", "n", "false", "no", "cancel", "fail", "wrong");

    /**
     * 将具有特定含义的字符串表达式转换为逻辑值
     */
    public static function CBool($str) {
        $key = Strings::LCase($str);

        if (array_key_exists($key, Conversion::$TRUEs)) {
            return True;
        } elseif (array_key_exists($key, Conversion::$FALSEs)) {
            return False;
        } else {
            return boolval($str);
        }
    }

    /**
     * @str 参数其实不止是字符串类型，其他的类型也能够传递进入这个函数之中
     */
    public static function CStr($str) {
        return (string) $str;
    }

    public static function CSng($str) {
        return floatval($str);
    }
}

?>