<?php

class StringHelpers {

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
}
?>