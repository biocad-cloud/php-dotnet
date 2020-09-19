<?php 

imports("Microsoft.VisualBasic.Strings");

/**
 * 模拟VisualBasic之中的一些简单的数据类型转换关键词
 * 
 * 这个模块中的所有的函数都是安全的，类似于VB中的值对象的默认值，空值在数值上都会被转换为零，逻辑值转换则为false
*/ 
class Conversion {

    /**
     * 这个函数安全的将字符串转换为数值类型  
     * 
     * @exp: 任何一非数字开始的字符串都会被解析为0
    */
    public static function Val($exp) {
        if (empty($exp)) {
            return 0.0;
        }

        // 首先解析出Double格式的字符串，这个解析出来的字符串前面可以包含有空格部分
        // 空格部分会在计算之前被trim去除掉
        preg_match("^\s*-?\d+(\.\d+)?", $exp, $matches, PREG_OFFSET_CAPTURE, 3);

        $exp = $matches[0];
        $exp = trim($exp);
        $value = Conversion::CDbl($exp);

        return $value;
    }

    /**
     * Alias of the ``intval`` function.
    */
    public static function CInt($str) {
        if (empty($str)) {
            return 0;
        } else {
            return intval($str);
        }
    }

    /**
     * 非安全的将字符串转换为Double类型的实数，可能会因为字符串的格式问题而出错
     */
    public static function CDbl($str) {
        if (empty($str)) {
            return 0.0;
        } else {
            return doubleval($str);
        }
    }

    /**
     * 枚举所有表示True含义的字符串
    */
    private static $TRUEs  = [
        "t"       => true, 
        "y"       => true, 
        "true"    => true, 
        "yes"     => true, 
        "ok"      => true, 
        "success" => true, 
        "right"   => true
    ];
    /**
     * 枚举所有表示False含义的字符串
    */
    private static $FALSEs = [
        "f"      => false, 
        "n"      => false, 
        "false"  => false, 
        "no"     => false, 
        "cancel" => false, 
        "fail"   => false, 
        "wrong"  => false
    ];

    /**
     * 判断目标字符串是否是一个用于表示一个逻辑值的字符串格式
     * 
     * @param string $str 任意的非空字符串
     * @return boolean 字符串是否表示一个逻辑值？
    */
    public static function isBoolFactorString($str) {
        if (empty($str) || $str == "") {
            return false;
        } else if (array_key_exists(strtolower($str), Conversion::$TRUEs)) {
            return true;
        } else if (array_key_exists(strtolower($str), Conversion::$FALSEs)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 将具有特定含义的字符串表达式转换为逻辑值
     * 
     * @param string $str 大小写不敏感
     * 
     * @return bool 返回字符串的字面含义所对应的逻辑值
    */
    public static function CBool($str) {
        $key = null;

        if (empty($str)) {
            return false;
        } elseif(is_bool($str)) {
            return $str;
        } elseif (is_integer($str) && $str == 1) {
            return true;
        } elseif (is_integer($str) && $str == 0) {
            return false;
        } else {
            if ($str === "1" || $str == "✔") {
                return true;
            } else if ($str === "0") {
                return false;
            } else {
                $key = Strings::LCase($str);
            }            
        } 

        if (array_key_exists($key, Conversion::$TRUEs)) {
            return True;
        } else if (array_key_exists($key, Conversion::$FALSEs)) {
            return False;
        } else {
            // 至少需要php 5.5版本           
            // return boolval($str);

            # echo "Try to convert to boolean: \n\n";
            # echo var_dump($str);
            # echo "\n\n";
            # echo var_dump($key);

            if ($str) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @str 参数其实不止是字符串类型，其他的类型也能够传递进入这个函数之中
     */
    public static function CStr($str) {
        return (string) $str;
    }

    public static function CSng($str) {
        if (empty($str)) {
            return 0.0;
        } else {
            return floatval($str);
        }
    }
}