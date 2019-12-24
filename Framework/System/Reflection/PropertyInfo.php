<?php

namespace System\Reflection {

    imports("php.DocComment");

    class PropertyInfo {

        /**
         * @var \ReflectionProperty
        */
        var $base;
        /** 
         * @var \PHP\PropertyDoc
        */
        var $doc;

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
         * @param \System\Type $declareOn
        */
        public function __construct($property, $declareOn) {
            $this->base          = $property;
            $this->Name          = $property->name;
            $this->ReflectedType = $declareOn;
            $this->doc           = $property->getDocComment();
            $this->doc           = \PHP\PropertyDoc::ParsePropertyDoc($this->doc);
            $this->PropertyType  = $this->GetCustomAttribute("var");
        }

        public function GetValue(object $obj) {
            return $obj->{$this->Name};
        }

        /** 
         * 要搜索的属性的类型。仅返回可分配给此类型的属性。
         * 
         * 注释文档之中的标签将会作为自定义属性来使用，标签对象的实例
         * 会根据标签的名称进行创建
         * 
         * 自定义属性class类型的构造函数必须要有两个参数：
         * 
         * + 第一个参数为property，即当前的自定义属性的申明属性对象
         * + 第二个参数为array，即该自定义属性的属性值的来源
         * 
         * @param string $type 标签的名称，例如``@access``的标签，可以填写``access``。
         *      
         * 有一些系统自带的标签需要额外注意一下：``@var``标签会直接返回目标类型的``\System\Type``实例
         * 
         * @return object 返回目标标签对象
        */
        public function GetCustomAttribute(string $type) {
            $tagData = \Utils::ReadValue($this->doc->tags, $type);

            if (empty($tagData)) {
                return null;
            } else {
                if ($type === "var") {
                    $class = $tagData["description"];
                    return \System\Type::GetClass($class);
                } else {
                    # 在这里动态的创建自定义属性对象
                    return new $type($this, $tagData);
                }
            }
        }
    }
}