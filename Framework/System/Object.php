<?php

namespace System {

    /**
     * The very base object type.
    */
    abstract class TObject {

        /** 
         * 获取当前的对象实例的类型信息
         * 
         * @return \System\Type
        */
        public function GetType() {
            imports("System.Type");
            # 在函数之中动态加载模块，可以尽量减少服务器的I/O负担
            return \System\Type::TypeOf($this);
        }
    
        public abstract function ToString();

        public function __toString() {
            return $this->ToString();
        }
    }
}