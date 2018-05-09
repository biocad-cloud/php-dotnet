<?php

Imports("System.Collection.ArrayList");
Imports("System.Text.RegularExpressions.Regex");
Imports("Microsoft.VisualBasic.Extensions.StringHelpers");
Imports("Microsoft.VisualBasic.Strings");

namespace MVC\Views {

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

        public static function InterpolateTemplate($html, $vars) {
            # 首先使用正则表达式解析出文档碎片之中的模板
            $pattern   = "<foreach @.+?>.+?</foreach>";
            $templates = \Regex::Matches($html, $pattern); 

            # 没有找到任何模板
            if (count($templates) === 0) {
                return $html;
            }

            foreach($templates as $template) {
                $var  = \explode(">", $template)[0];
                $var  = \explode("@", $var);
                $name = end($var);
                $var  = $vars[$name];
                $list = "";

                if (!$var) {
                    # 目标模板的数据源不存在
                    # 则将占位符替换为空白字符串
                    $list = "";
                } else {
                    $list = self::Build($var, $template, $name);                    
                }

                $html = Strings::Replace($html, $template, "");
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

            if (count($vars) == 0) {
                # 没有定义模板变量？？
                return "";
            } else {

                # 将索引名称都取出来
                $replaceAs = [];

                foreach($vars as $var) {
                    $name = StringHelpers::GetStackValue($var, '"', '"');
                    $replaceAs[$name] = $var;
                }

                $list = new ArrayList();

                foreach ($array as $row) {
                    $str = $template;

                    foreach ($replaceAs as $name => $index) {
                        $str = Strings::Replace($str, $index, $row[$name]);
                    }

                    $list->Add($str);
                }

                return Strings::Join($list->ToArray(), "\n\n");
            }
        }
    }
}
?>