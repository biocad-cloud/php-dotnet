<?php

namespace MVC\Views {

    Imports("MVC.View.foreach");
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
                $Xml = \XmlParser::LoadFromXml($template);
                
            }
        }

    }
}

?>