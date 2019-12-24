<?php

imports("System.Diagnostics.StackTrace");

/** 
 * 为枚举提供基类。
*/
abstract class Enum {

    /** 
     * 类型枚举缓存
     * 
     * @var array
    */
    private static $enumTypes = [];
    
    /** 
     * 从枚举名称之中解析出枚举值
     * 
     * @return integer 
    */
    public static function TryParse($name) {
        $key    = strtolower($name);
        $parser = self::getEnumType()["TryParse"];
        
        return $parser[$key];
    }

    private static function getEnumType() {
        $stack = StackTrace::GetCallStack();
        $type  = $stack->GetFrame(3);
        $type  = $type->frame["class"];

        if (!array_key_exists($type, self::$enumTypes)) {
            $reflector = new ReflectionClass($type);
            $dataEnum  = $reflector->getConstants();
            $toString  = [];
            $tryParse = [];

            foreach($dataEnum as $name => $val) {
                $toString["T". strval($val)] = $name;
                $tryParse[strtolower($name)] = $val;
            }

            self::$enumTypes[$type] = [
                "ToString" => $toString,
                "TryParse" => $tryParse
            ];
        }

        return self::$enumTypes[$type];
    }

    /**
     * 这个函数会自动根据栈信息来查找枚举类型
     * 
     * 调用函数必须要定义在枚举类之中，否则会找不到正确的栈片段信息
     * 
     * @param integer $value 枚举值
     * 
     * @return string 枚举值的名称字符串
    */
    public static function ToString($value) {
        $key   = "T" . strval($value);
        $names = self::getEnumType()["ToString"]; 

        return $names[$key];
    }
}