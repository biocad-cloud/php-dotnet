<?php

imports("System.Text.StringBuilder");

/**
 * ``@argv``标签解释器
*/
class Argv {

    /**
     * 命令行之中的参数名称
     * 
     * @var string
    */
    public $argument;
    /** 
     * 参数值的类型定义
     * 
     * + file: 参数的字符串值为一个文件路径
     * + boolean
     * + integer
     * + double
     * + string
     * 
     * @var string
    */
    public $type;

    /**
     * 这个参数所属的class的属性名称
     * 
     * @var \System\Reflection\PropertyInfo
    */
    public $property;

    /** 
     * @param array $tagData 使用反射方法读取得到的标签数据
    */
    public function __construct($property, $tagData) {
        $this->argument = $tagData["value"];
        $this->type     = trim($tagData["description"]);
        $this->type     = explode(" ", $this->type);
        $this->type     = end($this->type);
        $this->property = $property;
    }

    /**
     * 使用这个函数来构建生成命令行字符串
     * 
     * @param object $args 一个class对象实例，所有带有``@argv``标签的属性的值
     *       都会被读取用来生成命令行参数字符串
     * 
     * @return string 从目标对象实例所构建出来的命令行字符串
    */
    public static function CLIBuilder($args) {
        $type = \System\Type::TypeOf($args);
        $cli  = new StringBuilder();

        foreach($type->GetProperties() as $property) {
            $argv = $property->GetCustomAttribute("argv");

            if (!empty($argv)) {
                $value = $property->GetValue($args);
                
                if (!empty($value)) {
                    $token = self::BuildArgument($argv, $value);
                    $cli->AppendLine($token);
                }
            }
        }

        return $cli->ToString();
    }

    /** 
     * @param Argv $argv
    */
    private static function BuildArgument($argv, $value) {
        $name  = $argv->argument;

        // 在目标属性上面存在值，则会生成命令行字符串
        if ($argv->type === "boolean" && $value == true) {
            return $name;
        } else if ($argv->type === "string") {
            return "$name \"$value\"";
        } else if ($argv->type === "base64") {
            $value = base64_encode($value);
            return "$name \"$value\"";
        } else {
            return "$name $value";
        }
    }
}