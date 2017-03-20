<?php

/**
 * dotnet package manager, you must include this module at first.
 */
class dotnet {

/**
 * 对于这个函数额调用者而言，就是获取调用者所在的脚本的文件夹位置
 * @mod: 相对应调用者所处的脚本的位置而言的相对文件路径
 */
public static function Imports($mod) {
    
    $stack = debug_backtrace();
    $firstFrame = $stack[count($stack) - 1];
    $initialFile = $firstFrame['file'];

    return $initialFile;
}

}

?>