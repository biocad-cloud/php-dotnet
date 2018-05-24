<?php

namespace MVC\Views {

    # 2018-5-9 PHP Fatal error:  Namespace declaration statement has to be the very first statement or after any declare call in the script
    # 命名空间的申明前面不可以存在其他的php语句
    # 所以这些imports必须要放在namespace的里面

    Imports("System.Collection.ArrayList");
    Imports("System.Text.RegularExpressions.Regex");
    Imports("Microsoft.VisualBasic.Extensions.StringHelpers");
    Imports("Microsoft.VisualBasic.Strings");

    /**
     * 根据HTML文档之中所定义的模板来生成列表或者表格
    */
    class ForEachView {

        /**         
         * <ul>
         *
         *     <foreach @balance>
         *         <li>@balance["time"] &nbsp; @balance["title"]
         *		       <span style='text-align: right; color: @balance["color"]'>@balance["amount"] 元</span>
         *		   </li>
         * 	   </foreach>
         * 
         * </ul>
        */

        public static function ParseTemplates($html) {
            # 首先使用正则表达式解析出文档碎片之中的模板
            # flags表示正则表达式引擎忽略大小写并且以单行模式工作
            $pattern   = "<foreach(.*?)<\/foreach>";
            $flags     = "is";
            $templates = \Regex::Matches($html, $pattern, null, $flags); 

            # echo $html . "\n";
            # echo $pattern . "\n";
            echo \var_dump($templates);

            return $templates;
        }

        public static function InterpolateTemplate($html, $vars) {
            $templates = self::ParseTemplates($html);

            # 没有找到任何模板
            if (!$templates || count($templates) === 0) {
                return $html;
            }

            foreach($templates as $template) {
                $var  = \explode(">", $template)[0];
                $var  = \explode("@", $var);
                $name = end($var);
                $var  = $vars[$name];                

                if (!$var) {
                    # 目标模板的数据源不存在
                    # 则将模板保留下来，不做任何处理
                    
                    # DO NOTHING
                } else {
                    $templ = \StringHelpers::GetStackValue($template, ">", "<");
                    $list  = self::Build($var, $templ, $name);
                    $html  = \Strings::Replace($html, $template, $list);                
                }                
            }

            return $html;
        }

        /**
         * 根据模板生成列表或者表格
         * 
         * @param string $template 从HTML文档之中所解析出来的模板，这个模板字符串是已经去除了首尾的foreach标签了的
         * @param array $array 用来生成列表或者表格的数据源
         * @param string $var 在模板之中的数组变量名称
        */
        public static function Build($array, $template, $var) {
            $varPattern = "@$var\[\".+?\"\]";
            $vars = \Regex::Matches($template, $varPattern);

            # echo var_dump($array) . "\n\n";
            # echo $template . "\n\n";
            # echo $var . "\n\n";

            if (count($vars) == 0) {
                # 没有定义模板变量？？
                return "";
            } else {

                # 将索引名称都取出来
                $replaceAs = [];

                foreach($vars as $var) {
                    $name = \StringHelpers::GetStackValue($var, '"', '"');
                    $replaceAs[$name] = $var;
                }

                $list = new \ArrayList();

                foreach ($array as $row) {
                    $str = $template;

                    foreach ($replaceAs as $name => $index) {
                        $str_val = $row[$name];
                        $str = \Strings::Replace($str, $index, $str_val);
                    }

                    $list->Add($str);
                }

                return \Strings::Join($list->ToArray(), "\n\n");
            }
        }
    }
}
?>