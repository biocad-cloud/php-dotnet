<?php

Imports("Microsoft.VisualBasic.CommandLine.CommandLineParser");

/**
 * 这个模块是为了统一查询参数的获取方式而设定的
 * 
 * 为了调试php模块，有些时候是会需要从命令行进行脚本运行，而生产的时候目标脚本又
 * 只可能接受URL query。所以代码可能会需要修改，而使用这个模块之后，可以在参数的
 * 获取方法上，将命令行获取参数以及GET/POST获取参数的方法统一起来
*/
class Arguments implements ArrayAccess {

    private $args;

    /**
     * 在这个构造函数之中将命令行参数，GET/POST参数都合并在一起
    */
    function __construct() {

        # 基于本应用程序框架下的命令行的标准格式应该为
        #
        # script.php appName arg1=value1 arg2=value2 arg3=value3 ...
        $argvs = CommandLineParser::ParseCLIArgvs();
        $args  = [];

        foreach ([$_GET, $_POST, $argvs->arguments] as $env) {
            foreach ($env as $name => $value) {
                $args[$name] = $value;
            }
        }
    }

    #region "implements ArrayAccess"

    public function offsetSet($offset, $value) {
        $args[$offset] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->args[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->args[$offset]);
    }

    public function offsetGet($offset) {
        if (isset($this->args[$offset])) {
           return $this->args[$offset];
        } else {
           return null;
        }
    }

    #endregion
}

?>