<?php

namespace System\Reflection {

    class PropertyInfo {

        /**
         * @var \ReflectionProperty
        */
        var $base;

        /**
         * 获取当前成员的名称。
         * 
         * @var string
        */
        public $Name;

        /**
         * 获取用于获取此实例的类对象 MemberInfo。
         * 
         * @var \System\Type
        */
        public $ReflectedType;

        /**
         * 获取此属性的类型。
         * 
         * @var \System\Type
        */
        public $PropertyType;

        /**
         * @param \ReflectionProperty $property
        */
        public function __construct($property) {
            $this->base = $property;
            $this->Name = $property->name;
        }

        /** 
         * 要搜索的属性的类型。仅返回可分配给此类型的属性。
        */
        public function GetCustomAttribute(string $type) {

        }
    }
}