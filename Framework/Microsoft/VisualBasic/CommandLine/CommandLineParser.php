<?php

imports("Microsoft.VisualBasic.CommandLine.CommandLine");
imports("Microsoft.VisualBasic.Extensions.StringHelpers");
imports("Microsoft.VisualBasic.Strings");
imports("php.Utils");

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
     * @return CommandLine
    */
    public static function ParseCLIArgvs() {
        $argv      = $_SERVER['argv'];
        $script    = $argv[0];                   // get script file name
        $name      = Utils::ReadValue($argv, 1); // get command name
        $arguments = [];

        if (APP_DEBUG && IS_CLI) {
            console::log("get commandline arguments:");
            console::dump($argv);
        }

        if (Strings::InStr($name, "=") > 0) {
            # no command name
            list($key, $value) = self::parseCmdToken($name);

            $name = "";
            $arguments[$key] = $value;
        } else {
            # do nothing
        }

        for ($i = 2; $i < count($argv); $i++) {
            list($key, $value) = self::parseCmdToken($argv[$i]);
            // push to array
            $arguments[$key] = $value;
        }

        return new CommandLine($name, $arguments, $script);
    }

    private static function parseCmdToken($term) {
        if (Strings::InStr($term, "=")) {           
            return Utils::Tuple(StringHelpers::GetTagValue($term, "="));
        } else {
            // is a logical boolean flag, and it is 
            // true if it is presented.
            return [$term, true]; 
        }
    }
}