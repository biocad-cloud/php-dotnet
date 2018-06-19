<?php

namespace MVC\Views {

    Imports("System.Text.RegularExpressions.Regex");
    Imports("Microsoft.VisualBasic.Strings");

    /**
     * 直接支持php内联标签
    */
    class InlineView {

        /**
         * 2018-6-15
         * 
         * 因为在进行字符串替换的时候，false会被直接替换为空白字符串
		 * 可能会导致脚本语法错误，所以逻辑值都需要转换为文本之后才
         * 可以使用这个内联脚本帮助函数
         * 
         * https://stackoverflow.com/questions/1309800/php-eval-that-evaluates-html-php
         * https://stackoverflow.com/questions/4389361/include-code-from-a-php-stream
        */
        public static function RenderInlineTemplate($template) {
            $config = ini_get('allow_url_include');
            $config = "1";

            # allow_url_include = On
            # echo "templates for inline scripting: \n\n";
            # echo $template;

            # echo "allow_url_include = $config";

            # 在这里需要根据服务器配置参数来决定代码的流程
            # 否则会报错
            if (empty($config) || ($config == "0") || ($config == 0)) {

                /*

                if (APP_DEBUG) {
                    $template = "<!-- Using eval() function as engine -->" . $template;
                }

                # 2018-5-21 使用eval()函数来执行会出现bug

                # include url 被禁用掉了
                # 使用eval函数
                return eval(' ?>' . $template . '<?php ');

                */

                $template = "<!-- PHP inline scripting required option ``allow_url_include = On`` -->" . $template;
                
                return $template;

            } else {

                if (APP_DEBUG) {
                    $template = "<!-- Using output buffer for dynamics includes -->" . $template;
                }

                # 有些替换的变量没有被替换掉，可能会导致php的内联表达式失败
                # 在这里进行检查，然后给出警告消息
                $missing = self::checkVars($template);

                if ($missing && count($missing) > 0) {
                    # 任然存在未被替换掉的变量，给出警告消息
                    echo self::warnings($missing);              
                }

                # 2018-6-16 try...catch not working?

                # try {

                ob_start();                

                # echo $template;

                // 需要服务器端开启
                // PHP Warning:  include(): data:// wrapper is disabled in the server configuration by allow_url_include=0
                include "data://text/plain;base64," . base64_encode($template);
                $output = ob_get_clean();

                if (empty($output) && !empty($template)) {
                    $notWorking = "<span style='color:red;'>ERROR: include inline => ob_get_clean() is not working!</span>";
                    $notWorking = $notWorking . "<br /><br /><br /><br />"; 
                    $template   = $notWorking . $template;
                }

                return $template;
                # } catch (Exception $ex) {
                #    return "<div style='color:red;'><code><pre>\n" . $ex . "</pre></code></div>" . $template;
                # }                
            }
        }

        private static function warnings($missing) {
            $missing = "<span style='color:red'><strong>" . \implode(", ", $missing) . "</strong></span>";
            return "Missing variable: $missing. And these missing variable may caused the inline scripting error!<br />";      
        }

        private static function checkVars($template) {
            $pattern = "[{][$].+?[}]";
            $flags   = "is";
            $matches = \Regex::Matches($template, $pattern);
            
            # echo var_dump($matches);

            if (!$matches || count($matches) == 0) {
                return [];
            }

            $vars = [];

            foreach($matches as $ref) {
                $ref = \StringHelpers::GetStackValue($ref, "{", "}");
                array_push($vars, $ref);
            }
            
            return $vars;
        }
    }
}
?>