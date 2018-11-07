<?php

namespace System {

    Imports("System.Reflection.PropertyInfo");

    /** 
     * 表示类型声明：类类型、接口类型、数组类型、值类型、枚举类型、类型参数、泛型类型定义，
     * 以及开放或封闭构造的泛型类型。
    */
    class Type extends TObject {

        /**
         * class name
         * 
         * @var string
        */
        public $Name;
        /**
         * @var string
        */
        public $Namespace;

        /**
         * @var \ReflectionClass
        */
        var $reflector;

        /** 
         * 搜索具有指定名称的公共方法。
         * 
         * @param string $methodName 包含要获取的公共方法的名称的字符串。
        */
        public function GetMethod(string $methodName) {
            return $this->reflector->getMethod($methodName);
        }

        /**
         * @return System\Reflection\PropertyInfo[]
        */
        public function GetProperties() {
            $list = $this->reflector->getProperties();
            $out  = [];

            foreach($list as $property) {
                array_push($out, new System\Reflection\PropertyInfo($property));
            }

            return $out;
        }

        /** 
         * 返回``namespace\class``全名称
         * 
         * @return string
        */
        public function ToString() {
            if (\Strings::Empty($this->Namespace)) {
                return $this->Name;
            } else {
                return "{$this->Namespace}\\{$this->Name}";
            } 
        }

        /**
         * @param object $obj any object
         * 
         * @return Type
        */
        public static function TypeOf($obj) {
            return self::GetClass(get_class($obj));
        }

        /** 
         * 从所提供的className来获取类型信息
         * 
         * @return Type
        */
        public static function GetClass(string $className) {
            $type = new Type();
            // 这里返回来的其实是full name
            // 需要做一下解析
            $type->reflector = new \ReflectionClass($className);
            $type->Namespace = $type->reflector->getNamespaceName();
            $type->Name      = $type->reflector->getShortName();
            
            return $type;
        }
    }
}