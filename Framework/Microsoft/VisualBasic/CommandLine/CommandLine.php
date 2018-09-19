<?php

class CommandLine implements ArrayAccess {

    public $name;
    public $arguments;

    function __construct($name = null, $arguments = null) {
        $this->name      = $name;
        $this->arguments = $arguments;
    }

    #region "implements ArrayAccess"

    public function offsetSet($offset, $value) {
        $this->arguments[$offset] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->arguments[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->arguments[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->arguments[$offset]) ? $this->arguments[$offset] : null;
    }

    #endregion


}

?>