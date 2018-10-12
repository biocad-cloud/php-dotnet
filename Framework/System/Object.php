<?php

namespace System {

    Imports("System.Type");

    /**
     * The very base object type.
    */
    abstract class TObject {

        public function GetType() {
            return Type::TypeOf($this);
        }
    
        public abstract function ToString();

        public function __toString() {
            return $this->ToString();
        }
    }
}