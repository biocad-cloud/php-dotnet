<?php

namespace System {

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
         * @var ReflectionClass
        */
        var $reflector;

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
            $type = new Type();
            // 这里返回来的其实是full name
            // 需要做一下解析
            $type->Name      = get_class($obj);
            $type->reflector = new \ReflectionClass($type->Name);
            $type->Namespace = self::split($type->Name);
            $type->Name      = $type->Namespace["class"];
            $type->Namespace = $type->Namespace["namespace"];

            return $type;
        }

        private static function split($fullName) {
            # string(11) "Foo\Bar\Baz"
            $tokens    = \Strings::Split($fullName, "\\");
            $name      = end($tokens);
            $namespace = \Enumerable::Take($tokens, count($tokens) - 1);
            $namespace = \Strings::Join($namespace, "\\");

            return [
                "class"     => $name, 
                "namespace" => $namespace
            ];
        }
    }
}