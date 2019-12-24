<?php

namespace PhpDotNet {

    imports("Microsoft.VisualBasic.Strings");

    /**
     * Helper for some common data type in .NET framework
    */
    class MSDN {

        /**
         * URL link about data type help
         * 
         * @return string
        */
        public static function url($fullName, $lang = "en-us") {
            $fullName = \Strings::LCase($fullName);
            return "https://msdn.microsoft.com/$lang/library/$fullName(v=vs.110).aspx?cs-save-lang=1&cs-lang=vb";
        }

        /**
         * 生成链接到指定的类型名称的说明文档的html文本段
         * 
         * @param string $fullName
         * @param string $lang
         * 
         * @return string 
        */
        public static function link($fullName, $lang = "en-us") {
            return "<a href='" . self::url($fullName, $lang) . "'>$fullName</a>";
        }
    }
}