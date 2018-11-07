<?php

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
     * @var string
    */
    public $propertyName;

    /** 
     * @param array $tagData 使用反射方法读取得到的标签数据
    */
    public function __construct($tagData) {
        echo var_dump($tagData);
    }

    /**
     * @return Argv[]
    */
    public static function ParseArguments($options) {

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

    }
}