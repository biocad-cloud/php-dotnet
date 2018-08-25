<?php

namespace MVC\Views {

    Imports("System.Text.RegularExpressions.Regex");
    Imports("MVC.View.foreach");
    Imports("php.Utils");
    Imports("php.Xml");

    /**
     * 支持部分的thinkphp的volist标签语法
    */
    class volistViews {

        public static function InterpolateTemplate($html, $vars) {
            $templates = ForEachView::ParseTemplates($html, "volist");

            # 没有找到任何模板
            if (!$templates || count($templates) === 0) {
                return $html;
            }

            foreach($templates as $template) {
                $Xml    = \XmlParser::LoadFromXml($template);
                $volist = $Xml["volist"]; 
                # 变量名称，必须要存在这个属性值，否则无法得到渲染的数据源
                $name = \Utils::ReadValue($volist, "name");

                if (\Strings::Empty($name)) {
                    $template = "<pre>$template</pre>";
                    $msg      = "volist syntax error: variable name is missing.
                        <br />
                        <br />$template<br />";
                    \dotnet::ThrowException($msg);
                } else {
                    $src  = \Utils::ReadValue($vars, $name);
                    $fill = self::processTemplate($volist, $template, $src);
                    $html = \str_replace($template, $fill, $html); 
                }                
            }

            return $html;
        }

        private static function processTemplate($volist, $template, $array) {
            if (empty($array) || count($array) == 0) {
                # 如果变量数组是空的时候的替代值
                $name  = $volist["name"];
                $empty = \Utils::ReadValue(
                    $volist, "empty", 
                    "<span style='color:red;'>Empty volist=$name</span>"
                );

                return $empty;
            }
            
            # 对变量名称的重命名，如果不存在，则直接使用原始变量名来进行命名
            $id      = \Utils::ReadValue($volist, "id", $volist["name"]);
            $pattern = '{[$]' . $id . "\..+?}";
            $vars    = \Regex::Matches($template, $pattern);

            if (empty($vars) || count($vars) == 0) {
                # 没有定义模板变量？？
                return $template;
            } else {
                return self::buildImpl(
                    $array, $template, self::nameslist($vars)
                );
            }
        }

        private static function nameslist($vars) {
            $names = [];

            foreach($vars as $ref) {
                $ref0 = $ref;
                $ref  = trim($ref, '{}');
                $ref  = \StringHelpers::GetTagValue($ref, ".");

                list($nil, $ref) = \Utils::Tuple($ref);

                $names[] = [
                    "ref"  => $ref0, 
                    "name" => $ref
                ];
            }

            return $names;
        }

        private static function buildImpl($array, $template, $vars) {
            $html = "";

            foreach($array as $var) {
                $templ = $template . "";

                foreach($vars as $ref => $name) {
                    $val   = \Utils::ReadValue($var, $name, "");                   
                    $templ = str_replace($ref, $val, $templ);
                }

                $html = $html . "\n" . $templ;
            }

            return $html;
        }
    }
}

?>