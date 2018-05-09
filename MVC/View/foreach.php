<?php

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
            # 首先使用正则表达式解析出文档碎片
        }

        /**
         * 根据模板生成列表或者表格
         * 
         * @param string $template 从HTML文档之中所解析出来的模板
         * @param array $array 用来生成列表或者表格的数据源
         * @param string $var 在模板之中的数组变量名称
        */
        public static function Build($array, $template, $var) {
            $varPattern = "@$var\[\".+?\"\]";
        }
    }
}
?>