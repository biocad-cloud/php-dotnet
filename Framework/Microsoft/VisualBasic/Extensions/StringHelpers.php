<?php

Imports("Microsoft.VisualBasic.Strings");

/**
 * String helper extensions
*/
class StringHelpers {

    /** 
     * 只匹配第一次出现的查找字符串
     * 
     * @param string $needle 所查找的将要被替换掉的字符串
     * @param string $replace 将要替换掉目标字符串的字符串值
     * @param string $haystack 需要进行字符串替换操作的母字符串
     * 
     * @return string
    */
    public static function str_replace_once($needle, $replace, $haystack) {
        $pos = strpos($haystack, $needle); 

        if ($pos === false) { 
            return $haystack; 
        } else {
            return substr_replace($haystack, $replace, $pos, strlen($needle)); 
        }     
    }        

    /**
     * Text split by new line
     * 
     * @param string $text
     * 
     * @return array
    */
    public static function LineTokens($text) {
        return preg_split("/(\r|\n)+/", $text);
    }

    /**
     * 判断目标字符串是否是目标正则表达式所表示的模式
     * 
     * @param string $str
     * @param string $pattern 不需要添加//，这个函数会自动添加/包裹字符串
     * 
     * @return boolean
    */
    public static function IsPattern($str, $pattern) {
        $matches = null;
        $pattern = "/$pattern/";

        preg_match(
            $pattern, 
            $str, 
            $matches, 
            PREG_OFFSET_CAPTURE
        );

        if (empty($matches) || $matches === false || count($matches) !== 1) {
            return false;
        } else {
            $pattern = $matches[0];
            $pattern = $pattern[0];

            return $pattern == $str;
        }
    }

    /**
     * 使用这个函数可以非常方便的从字符串之中取出由一对临近的括号或者引号
     * 所包裹的子字符串
     * 
     * @param string $str
     * @param string $left
     * @param string $right
     * 
     * @filesource https://github.com/xieguigang/sciBASIC/blob/cebfca8ad0f7e565a00774bb3507796c8e72ecc6/Microsoft.VisualBasic.Core/Extensions/StringHelpers/StringHelpers.vb#L622
    */
    public static function GetStackValue($str, $left, $right) {
        if (Strings::Len($str) <= 2) {
            return "";
        }

        $p = Strings::InStr($str, $left) + Strings::Len($left);
        $q = Strings::InStrRev($str, $right);

        if ($p == 0 && $q == 0) {
            return $str;
        } else if ($p >= $q) {
            return "";
        } else {
            $str = Strings::Mid($str, $p, $q - $p);
            return $str;
        }
    }

    /**
     * Text parser for the format: ``tagName{<paramref name="delimiter"/>}value``
     * 这个函数返回一个tuple:  ``[key => value]``
     * 
     * @param string $str
     * @param string $delimiter
     * 
     * @return array ``[key => value]``
    */
    public static function GetTagValue($str, $delimiter = " ") {
        if (empty($str)) {
            return [];
        }

        $p = Strings::InStr($str, $delimiter);

        if ($p === 0) {
            return [$str => ""];
        }

        $key   = Strings::Mid($str, 1, $p - 1);
        $value = Strings::Mid($str, $p + Strings::Len($delimiter));

        return [$key => $value];
    }

    /**
     * 在字符串前面填充指定长度的00序列，假若输入的字符串长度大于fill的长度，
     * 则不再进行填充
     * 
     * @param mixed $n
     * @param string $fill
     * 
     * @return string 
    */
    public static function FormatZero($n, $fill = "00") {
        $s = strval($n);
        $d = Strings::Len($fill) - Strings::Len($s);

        if ($d < 0) {
            return $s;
        } else {
            return Strings::Mid($fill, 1, $d) . $s;
        }
    }    
}
?>