<?php

namespace PhpDotNet {

    /**
     * 内部模块文件加载程序
    */
    class bootstrap {

        /**
         * Imports the external library modules at here?
        */
        private static function moduleImports($module) {
            $info = debug_backtrace(); 

            foreach($info as $k => $v) { 
                // 解析出当前的栈片段信息
                if (self::isImportsCall($v)) {                        
                    // 当前的栈信息不是Imports，则可能是调用Imports函数的脚本文件
                    $file   = $v["file"];
                    $dir    = dirname($file);
                    $module = trim($module, "*");
                    $module = "$dir/$module"; 

                    break;
                }
            }

            # 导入该文件夹下的所有模块文件
            # 这个模式可以不要求是php.NET模块
            return self::importsAlls($module);
        }

        private static function getInternalModuleRefer($module) {
            $module = str_replace(".", "/", $module);             

            # 2018-5-15 假若Imports("MVC.view");
            # 因为文件结构之中，有一个view.php和view文件夹
            # 所以在这里会产生冲突
            # 在linux上面因为文件系统区分大小写，所以可以通过大小写来避免冲突
            # 但是windows上面却不可以
            # 在这里假设偏向于加载文件
            $php = PHP_DOTNET . "/{$module}.php";

            # 如果是文件存在，则只导入文件
            if (file_exists($php)) {
                $module = $php;
            } elseif (file_exists($php = PHP_DOTNET . "/$module/index.php")) {
                # 如果不存在，则使用index.php来进行判断
                $module = $php;
            } 

            if (is_dir($dir = PHP_DOTNET . "/$module/")) {
                # 可能是一个文件夹
                # 则认为是导入该命名空间文件夹下的所有的同级的文件夹文件
                return ["true"  => dirname($dir)];
            } else {
                return ["false" => $module];
            }
        }

        /**
         * @param string $module
         * 
         * @return string 返回所导入的文件的全路径名
        */
        public static function LoadModule($module) {
            // 因为WithSuffixExtension这个函数会需要依赖小数点来判断文件拓展名，
            // 所以对小数点的替换操作要在if判断之后进行  
            if (\Utils::WithSuffixExtension($module, "php")) {
                $module = str_replace(".", "/", $module); 
                $module = PHP_DOTNET . "/{$module}";
            } else if (\Strings::EndWith($module, "/*")) {
                self::moduleImports($module);
            } else {
                list($isdir, $module) = \Utils::Tuple(
                    self::getInternalModuleRefer($module)
                );

                if ($isdir === "true") {
                    self::importsAlls($module);
                } else {
                    self::importsImpl($module);
                }
            }
        
            return $module;
        }

        /**
         * 在这里导入需要导入的模块文件
         * 
         * @param string $module php文件的路径
        */
        private static function importsImpl($module) {
            include_once($module);
                    
            if (!APP_DEBUG) {
                return;
            }

            $initiator = [];

            foreach(debug_backtrace() as $k => $v) { 
                // 解析出当前的栈片段信息
                if (self::isImportsCall($v)) {
                    $initiator = $v["file"];
                    break;
                }
            }
            
            if (!\dotnet::$debugger) {
                \dotnet::$debugger = new \dotnetDebugger();
            }

            \dotnet::$debugger->add_loaded_script($module, $initiator);
        }

        /**
         * 判断当前的这个栈片段信息是否是Imports函数调用？
         * 
         * 在这个函数之中会忽略掉package.php和bootstrap.php这两个文件之中的函数调用
         * 
         * @param array $frame 一个栈片段信息
        */
        private static function isImportsCall($frame) {
            $fileName  = basename($frame["file"]);
            $funcName  = $frame["function"];
            $args      = $frame["args"];
            $is_dotnet = self::isDotNetClass($frame);

            if ($fileName == basename(__FILE__) || $fileName == "package.php") {
                return false;
            } else if ($funcName !== "Imports" || $funcName == "LoadModule") {
                return false;
            } else if (count($args) != 1) {
                return false;
            } else if ($is_dotnet) {
                return false;
            } else {
                return true;
            }
        }

		const DOTNET    = "dotnet";
		const BOOTSTRAP = "PhpDotNet\\bootstrap";
		
        private static function isDotNetClass($frame) {
            if (!array_key_exists("class", $frame)) {
                return false;
            } else {
				$class  = $frame["class"];
                $assert = $class === self::DOTNET || $class === self::BOOTSTRAP;
				
				return $assert;
            }
        }

        /**
         * 导入目标命名空间文件夹之下的所有的php模块文件
         * 
         * @param string $directory 包含有module文件的文件夹的路径
        */
        private static function importsAlls($directory) {
            $files = [];
            $dir   = opendir($directory);

            \console::log("Imports all module files from $directory");

            # 20180829 readdir函数返回来的文件名是不包含有文件夹路径的
            while ($dir && ($file = readdir($dir)) !== false) {
                if ($file == "." || $file == "..") {
                    continue;
                } else {
                    $file = "$directory/$file";
                }

                if (\Utils::WithSuffixExtension($file, "php")) {
                    self::importsImpl($file);
                    array_push($files, $file);
                } else if (is_dir($file)) {
                    # 如果是一个文件夹，则继续递归加载？
                    foreach(self::importsAlls($file) as $php) {
                        array_push($files, $php);
                    }
                }
            }

            closedir($dir);

            return $files;
        }
    }
}
