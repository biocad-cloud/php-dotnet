<?php

class Vector {

    /** 
     * @var object[]
    */
    private $sequence;

    public function getSize() {
        return count($this->sequence);
    }

    /** 
     * @param object[] $seq
    */
    public function __construct($seq) {
        $this->sequence = $seq;
    }

    #region "implements ArrayAccess"

    /** 
     * @param string $offset The key name in object
     * @param any|any[] $value The value or value sequence to set
     * 
     * @return void
    */
    public function offsetSet($offset, $value) {
        $len = $this->getSize();

        if (is_array($value)) {
            for($i = 0; $i < $len; $i++) {
                $this->sequence[$i][$offset] = $value[$i];
            }
        } else {
            for($i = 0; $i < $len; $i++) {
                $this->sequence[$i][$offset] = $value;
            }
        }
    }

    public function offsetExists($offset) {
        foreach ($this->sequence as $obj) {
            if (array_key_exists($offset, $obj)) {
                return true;
            }
        }

        return false;
    }

    public function offsetUnset($offset) {
        foreach ($this->sequence as $obj) {
            unset($obj[$offset]);
        }
    }

    public function offsetGet($offset) {
        $array = [];

        foreach($this->sequence as $obj) {
            $array[] = isset($obj[$offset]) ? $obj[$offset] : null;
        }
        
        return $array;
    }

    #endregion
}