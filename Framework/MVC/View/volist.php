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

            \debugView::LogEvent("Do volist interpolation...");
            
            if (APP_DEBUG && strlen($html) == 0) {
                \console::warn("Template data is empty!");
            }

            # 没有找到任何模板
            if (!$templates || count($templates) === 0) {
                \console::log("Not required for volist interpolation.");
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

        /**
         * <volist name="variable_name" id="alias" empty="empty_notices">
         * 
         * @param array $volist 将volist正则表达式的匹配模板结果进行XML格式的解析得到的数组
         * @param string $template volist的原始的XML模板字符串数据
         * @param array $array $volist之中的name所对应的变量值，这个是已经从所传递进来的数据源之中
         *                     使用name取出来了的用于填充volist的数组对象 
         * 
         * @return string 经过填充的html字符串
        */
        private static function processTemplate($volist, $template, $array) {
            if (empty($array) || count($array) == 0) {
                # 如果变量数组是空的时候的替代值
                $name  = $volist["name"];
                $empty = \Utils::ReadValue(
                    $volist, "empty", 
                    "<span style='color:red;'>Empty volist=$name</span>"
                );

                return $empty;
            } else {
                $template = \StringHelpers::GetStackValue($template, ">", "<");
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

        /**
         * 将从模板之中解析出来得到的变量引用的列表转换为``[ref => name]``
         * 的键值对
        */
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

        /**
         * 进行模板之中的循环变量的填充渲染处理
        */
        private static function buildImpl($array, $template, $vars) {
            $html = "";

            foreach($array as $var) {
                $templ = $template . "";

                foreach($vars as $ref => $name) {
                    $ref   = $name["ref"];
                    $name  = $name["name"];
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