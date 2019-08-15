<?php

namespace System {

    Imports("System.Object");
    Imports("System.Reflection.PropertyInfo");

    use \System\Reflection\PropertyInfo as Property;

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
         * 如果这个属性值为空值的话，说明当前的类型是诸如``string``, ``boolean``这类基础类型
         * 
         * @var \ReflectionClass
        */
        var $reflector;

        /** 
         * PHP supports ten primitive types.
         * 
         * > http://php.net/manual/en/language.types.intro.php
        */
        public static $primitiveTypes = [
#region "Four scalar types:"
            "boolean"  => false,
            "integer"  => 0,
            # float is equals to double
            "float"    => 0.0,
            "double"   => 0.0,
            "string"   => "",
#endregion
#region "Four compound types:"
            "array"    => [],
            "object"   => null,
            "callable" => null,
            "iterable" => null,
#endregion
#region "And finally two special types:"
            "resource" => null,
            "NULL"     => null,
#endregion

            // A very special primitive type
            "mixed"    => null
        ];

        /** 
         * 获取一个值，通过该值指示 ``System.Type`` 是否为基元类型之一。
         * 
         * @return boolean 如果 true 为基元类型之一，则为 ``System.Type``；否则为 false。
        */
        public function IsPrimitive() {
            if (empty($this->reflector)) {
                return true;
            } else {
                return false;
            }
        }

        /** 
         * 搜索具有指定名称的公共方法。
         * 
         * @param string $methodName 包含要获取的公共方法的名称的字符串。
        */
        public function GetMethod(string $methodName) {
            if (empty($this->reflector)) {
                return null;
            } else {
                return $this->reflector->getMethod($methodName);
            }
        }

        /**
         * @return \System\Reflection\PropertyInfo[]
        */
        public function GetProperties() {
            if (empty($this->reflector)) {
                # 基础类型无方法
                return [];
            } else {
                $list = $this->reflector->getProperties();
                $out  = [];
                $p    = null;
    
                foreach($list as $property) {
                    array_push($out, new Property($property, $this));
                }
    
                return $out;
            }
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
         * @param string $className 在这里可能会需要处理基础类型，否则会出现错误：
         * 
         * ```php
         * # PHP Fatal error:  Uncaught ReflectionException: Class string does not exist
         * ```
         * 
         * @return Type
        */
        public static function GetClass(string $className) {
            $type = new Type();

            if (\Strings::EndWith($className, "[]")) {
                // is an array
                $className = "array";
            }

            if (array_key_exists($className, self::$primitiveTypes)) {
                # 是基础类型
                $type->Namespace = "\\";
                $type->Name      = $className;
            } else {
                // 这里返回来的其实是full name
                // 需要做一下解析
                $type->reflector = new \ReflectionClass($className);
                $type->Namespace = $type->reflector->getNamespaceName();
                $type->Name      = $type->reflector->getShortName();
            }

            return $type;
        }
    }
}