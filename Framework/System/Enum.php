<?php

Imports("System.Diagnostics.StackTrace");

class Enum {

    /** 
     * 类型枚举缓存
     * 
     * @var array
    */
    private static $enumTypes = [];

    /**
     * 这个函数会自动根据栈信息来查找枚举类型
     * 
     * 调用函数必须要定义在枚举类之中，否则会找不到正确的栈片段信息
     * 
     * @param integer $value 枚举值
    */
    public static function ToString($value) {
        $stack     = StackTrace::GetCallStack();
        $type      = $stack->GetFrame(2);
        $type      = $type->frame["class"];
        $reflector = new ReflectionClass($type);
        
        if (!array_key_exists($type, self::$enumTypes)) {
            $dataEnum = $reflector->getConstants();
            $toString = [];

            foreach($dataEnum as $name => $value) {
                $toString[strval($value)] = $name;
            }

            self::$enumTypes[$type] = $toString;
        }

        return self::$enumTypes[$type][strval($value)];
    }
}