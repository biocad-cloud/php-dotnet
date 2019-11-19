<?php

Imports("Microsoft.VisualBasic.CommandLine.CommandLine");
Imports("Microsoft.VisualBasic.Extensions.StringHelpers");
Imports("Microsoft.VisualBasic.Strings");
Imports("php.Utils");

/**
 * 命令行字符串解析器模块
*/
class CommandLineParser {

    /**
     * 
     * @return CommandLine
    */
    public static function ParseCLIInput($cli) {
        $name      = "";
        $arguments = [];

        return new CommandLine($name, $arguments);
    }

    /**
     * @abstract 因为php的argv数组之中的第一个元素总是当前脚本的文件名，
     *           所以在这里是从1开始的，即跳过第一个文件名，第二个元素
     *           （下标1）开始才是所需要的命令行数据
     *  
    */
    public static function ParseCLIArgvs() {
        $name      = $argv[1];
        $arguments = [];

        for ($i = 2; $i < count($argv); $i++) {
            $term = $argv[$i];

            if (Strings::InStr($term, "=")) {
                $term = StringHelpers::GetTagValue($term, "=");
                list($key, $value) = Utils::Tuple($term);
            } else {
                // is a logical boolean flag, and it is 
                // true if it is presented.
                list($key, $value) = [$term, true]; 
            }

            $arguments[$key] = $value;
        }

        return new CommandLine($name, $arguments);
    }
}