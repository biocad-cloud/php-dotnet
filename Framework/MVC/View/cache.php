<?php

namespace MVC\Views {

    class Cache {

        /**
         * 强制更新所有的页面视图模板缓存
         * 
         * 假设每一个文件夹的第一级之中的html文档都是视图的文档
        */
        public static function UpdateCache() {
            $views = \DotNetRegistry::GetMVCViewDocumentRootTable();

            foreach($views as $script => $directory) {
                
            } 
        }
    }
}