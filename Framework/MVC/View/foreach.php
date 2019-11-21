<?php

namespace MVC\Views {

    # 2018-5-9 PHP Fatal error:  Namespace declaration statement has to be the very first statement or after any declare call in the script
    # 命名空间的申明前面不可以存在其他的php语句
    # 所以这些imports必须要放在namespace的里面

    Imports("System.Collection.ArrayList");
    Imports("System.Linq.Enumerable");
    Imports("System.Text.RegularExpressions.Regex");
    Imports("Microsoft.VisualBasic.Extensions.StringHelpers");
    Imports("Microsoft.VisualBasic.Strings");
    Imports("php.Utils");

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

        /**
         * 首先使用正则表达式解析出文档碎片之中的模板
        */
        public static function ParseTemplates($html, $tagName = "foreach") {
            # flags表示正则表达式引擎忽略大小写并且以单行模式工作
            $pattern   = "<$tagName(.*?)<\/$tagName>";
            $flags     = "is";
            $templates = \Regex::Matches($html, $pattern, null, $flags); 

            return $templates;
        }

        /**
         * ``<foreach>``标签可以嵌套
        */
        public static function StackParser($html) {
            $openStacks  = \Utils::Indices($html, "<foreach");
            $closeStack  = \Utils::Indices($html, "</foreach>");
            $tupleStream = array_merge(
                \Enumerable::Select($openStacks, function($i) { return [$i => "<"]; }),
                \Enumerable::Select($closeStack, function($i) { return [$i => ">"]; })
            );
            $tupleStream = \Enumerable::OrderBy($tupleStream, function($t) {
                return \Conversion::CInt(array_keys($t)[0]);
            });

            $templates  = [];
            $stackDepth = 0;
            $open_pos   = -1;

            foreach ($tupleStream as $flag) {
                list($i, $tag) = \Utils::Tuple($flag);

                if ($tag === "<") {
                    # open stack
                    $stackDepth = $stackDepth + 1;

                    if ($stackDepth == 1) {
                        $open_pos = $i;
                    }
                } else {
                    # close stack
                    $stackDepth = $stackDepth - 1;

                    if ($stackDepth < 0) {
                        # syntax error
                        throw new \exception("ForEach html template syntax error!");
                    } else if ($stackDepth == 0) {
                        # even, find a foreach template
                        $len   = $i - $open_pos + 10;
                        $templ = substr($html, $open_pos, $len);

                        array_push($templates, $templ);
                    }
                }
            }

            if ($stackDepth > 0) {
                # 仍然存在未闭合的区间，语法错误
                throw new \exception("ForEach html template syntax error!");
            }

            return $templates;
        }

        public static function InterpolateTemplate($html, $vars) {
            $templates = self::StackParser($html);

            if (APP_DEBUG && strlen($html) == 0) {
                \console::warn("Template data is nothing...");
            }

            # 没有找到任何模板
            if (!$templates || count($templates) === 0) {
                \console::log("No foreach template was found...");
                return $html;
            }

            foreach($templates as $template) {
                $var  = \explode(">", $template)[0];
                $var  = \explode("@", $var);
                $name = end($var);

                if (!array_key_exists($name, $vars)) {
                    # 目标模板的数据源不存在
                    # 则将模板保留下来，不做任何处理
                    
                    # DO NOTHING
                } else {

                    $var   = $vars[$name];    
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
            $pattern = "@$var\[\".+?\"\]";
            $vars = \Regex::Matches($template, $pattern);

            if (count($vars) == 0) {
                # 没有定义模板变量？？
                return "";
            } else {
                return self::buildImpl(
                    $array, $template, $var, $vars
                );
            }
        }

        private static function buildImpl($array, $template, $var, $vars) {
            # 将索引名称都取出来
            $replaceAs = [];

            foreach($vars as $var) {
                $name = \StringHelpers::GetStackValue($var, '"', '"');
                $replaceAs[$name] = $var;
            }

            $list    = new \ArrayList();
            $nesting = self::nestingTemplate($template);

            # echo var_dump($nesting);

            foreach ($array as $row) {
                $str = $template;

                foreach ($replaceAs as $name => $index) {
                    $str_val = \Utils::ReadValue($row, $name);

                    if (is_array($str_val)) {
                        # 可能是内嵌的模板的数据源

                    } else {
                        $str = \Strings::Replace($str, $index, $str_val);
                    }                        
                }
                foreach (self::BuildNesting($nesting, $row) as $templ => $nesting_page) {
                    $str = \Strings::Replace($str, $templ, $nesting_page);
                }

                $list->Add($str);
            }

            return \Strings::Join($list->ToArray(), "\n\n");
        }

        /**
         * 还有可能在这里面还存在嵌套？？？
        */
        public static function BuildNesting($nesting, $row) {
            # $var => [$refName => $templ]            
            $pages = [];

            foreach($nesting as $var => $templ) {
                list($ref, $templ) = \Utils::Tuple($templ);

                $template = $templ;
                $ref      = \Utils::ReadValue($row, $ref);
                $templ    = \StringHelpers::GetStackValue($templ, ">", "<");

                $varPattern = "@$var\[\".+?\"\]";
                $vars = \Regex::Matches($templ, $varPattern);

                $replaceAs = [];

                foreach($vars as $var) {
                    $name = \StringHelpers::GetStackValue($var, '"', '"');
                    $replaceAs[$name] = $var;
                }

                foreach ($replaceAs as $name => $index) {
                    $str_val = $ref[$name];
                    $templ   = \Strings::Replace($templ, $index, $str_val);
                }

                $pages[$template] = $templ;
            }

            # echo var_dump($pages);

            return $pages;
        }

        public static function nestingTemplate($template) {
            $nesting   = self::StackParser($template);
            $templates = [];

            foreach($nesting as $templ) {

                # 在这里将变量的名称，以及引用的表达式解析出来
                # <foreach @attrs='@list["attrs"]'>
                $var = \explode(">", $templ)[0];
                $var = \StringHelpers::GetTagValue($var, " ");
                $var = \Utils::Tuple($var)[1];
                $var = \StringHelpers::GetTagValue($var, "=");

                list($var, $ref) = \Utils::Tuple($var);

                # @attrs => attrs
                $var = \Strings::Mid($var, 2);
                # '@list["attrs"]' => attrs
                $ref = \StringHelpers::GetStackValue($ref, '"', '"');

                $templates[$var] = [$ref => $templ];
            }

            return $templates;
        }
    }
}
?>