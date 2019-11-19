<?php

Imports("Microsoft.VisualBasic.CommandLine.CommandLineParser");

/**
 * 这个模块是为了统一查询参数的获取方式而设定的
 * 
 * @abstract 为了调试php模块，有些时候是会需要从命令行进行脚本运行，而生产的时候目标脚本又
 * 只可能接受URL query。所以代码可能会需要修改，而使用这个模块之后，可以在参数的
 * 获取方法上，将命令行获取参数以及GET/POST获取参数的方法统一起来
 * 
 * @category 这个模块类型于$_REQUEST变量，只不过增加了对命令行参数的额外处理
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

    /**
     * 检查参数是否符合要求，并将参数取出至对应的属性之中
     * 
     * @param array $validations
     * 
     * @return array 
    */
    public static function EnsureParam($validations) {

        /*
            # 示例代码

            $check_array = array(
                'order_sn',
                'express_no'               => array('required'=>false),
                'uname',
                'uphone',
                'area_origin',
                'birthday',
                'sex'                      => array('type'=>'preg','preg'=>"/^1|2$/"),
                'height'                   => array('type'=>'number'),
                'weight'                   => array('type'=>'number'),
                'address',
                'is_smoke'                 => array('type'=>'preg','preg'=>"/^|2|1|0$/",'required'=>false),
                'is_drink'                 => array('type'=>'preg','preg'=>"/^|2|1|0$/",'required'=>false),
                'systolic_pressure'        => array('required'=>false),
                'dilatation_pressure'      => array('required'=>false),
                'medical_1'                => array('required'=>true),
                'medical_2'                => array('required'=>true),
                'medical_other'            => array('required'=>true),
                'disease_history'          => array('required'=>true),
                'familial_disease_history' => array('required'=>true),
            );

            $values = Arguments::EnsureParam($check_array);
        */

        $res  = [];              # 返回的参数列表
        $args = new Arguments(); # 所获取得到的输入的参数集合

        foreach($validations as $key => $param) {
            # 这个是参数名称，如果只有一个名称字符串，则说明其为必须参数
            # 默认检查参数不能为空

            if (is_string($param)) {
                $v = trim($args[$param]);

                if (empty($v)) {
                    throw new dotnetException("Required param '$param' can not be empty!");
                } else {
                    $res[$param] = $v;
                }
            } elseif (is_array($param)) {
                $res[$key] = self::solveParam($key, $args, $param);
            }
        }

        return $res;
    }

    private static function solveParam($key, $args, $param) {
        $required = isset($param['required']) ? $param['required'] : true;
        $method   = isset($param['method'])   ? $param['method']   : 'POST';
        $v        = $method == 'GET' ? $_GET[$key] : $_POST[$key];
        
        if($required && (!isset($args[$key]) || empty($v))) {
            throw new dotnetException("Param '$key' can not be null!");
        }

        $type = isset($param['type']) ? $param['type'] : '';

        switch($type) {
            case 'number':
                if (!is_numeric($v)) {
                    $message = "Param: '$key' type mismatched! (Numeric value was required!)";
                    throw new dotnetException($message);
                }
                break;
            case 'preg':
                $preg = $param['preg'];
                if (!empty($preg) && !@preg_match($preg, $v)) {
                    $message = "Param: '$key' mismatch. (Param value should match expression: '$preg')";
                    throw new dotnetException($message);
                }
                break;
            # case else : pass
        }

        return $v;
    }

    /**
     * A simple cli arguments parser, an example cli input format:
     * 
     * ```
     * php myscript.php --user=nobody --password=secret -p --access="host=127.0.0.1 port=456"
     * ```
    */
    public static function simpleArguments() {
        $_ARG = array();

        foreach ($argv as $arg) {
            if (ereg('--([^=]+)=(.*)', $arg, $reg)) {
                $_ARG[$reg[1]] = $reg[2];
            } elseif (ereg('-([a-zA-Z0-9])',$arg,$reg)) {
                $_ARG[$reg[1]] = 'true';
            }
        }

        return $_ARG;
    }
}