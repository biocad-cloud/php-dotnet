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
     * @return Argv[]
    */
    public static function ParseArguments($options) {

    }
}