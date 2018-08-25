<?php

namespace MVC\Views {

    Imports("MVC.View.foreach");

    class volistViews {

        public static function InterpolateTemplate($html, $vars) {
            $templates = ForEachView::ParseTemplates($html, "volist");

            # 没有找到任何模板
            if (!$templates || count($templates) === 0) {
                return $html;
            }
        }

    }
}

?>