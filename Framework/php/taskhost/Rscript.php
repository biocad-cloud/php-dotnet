<?php

namespace PHP\taskhost {

    /**
     * Rscript helper
    */
    class Rscript {

        /**
         * 在R之中表示空值的字符串枚举
         * 
         * @var string[]
        */
        private static $nullFactors = [
            "null", "NULL", "NA", "NaN", "Inf", "-Inf", "-"
        ];

        /**
         * 这个函数判断目标字符串输入是否是空值？
         * 
         * + 如果目标字符串为空值或者长度为零，则返回true
         * + 如果目标字符串不为空，但是字符串值为NULL,NA等在R脚本之中表示NULL的值的时候，也会被判断为空值
         * + 如果目标字符串全部都是空格或者TAB符号，则也会被判断为空
         * 
         * @param string $str 待判断的目标字符串
         * 
         * @return boolean
        */
        public static function IsNullOrEmpty($str) {
            if (empty($str) || (strlen(trim($str)) == 0)) {
                return true;
            }

            foreach(self::$nullFactors as $NULL) {
                if ($str === $NULL) {
                    return true;
                }
            }

            return false;
        }
    }
}