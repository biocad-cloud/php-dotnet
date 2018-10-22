<?php

Imports("System.Text.StringBuilder");

/**
 * 在StringBuilder的基础之上增加了更加方便的字符串替换方法
*/
class ScriptBuilder extends StringBuilder implements ArrayAccess {

    /**
     * @param string $string The initialize string buffer
    */
    public function __construct($string = NULL, $newLine = PHP_EOL) {
        parent::__construct($string, $newLine);
    }

    #region "implements ArrayAccess: charAt index function/symbol replacement"

    /**
     * Symbol expression: @symbol
     * 
     * @param integer $offset Char at index/symbol
     * @param string $value A char(string with one character)
    */
    public function offsetSet($offset, $value) {
        if (is_string($offset)) {
            # symbol replacement
            if ($offset[0] !== "@") {
                $symbol = "@" . $offset; 
            } else {
                $symbol = $offset;
            }

            parent::Replace($symbol, $value);
        } else {
            parent::offsetSet($offset, $value);
        }
    }

    /**
     * Find substring is exists on buffer or not?
     * 
     * @param integer $offset index should be positive integer and in range of the string length.
     * 
     * @return boolean
    */
    public function offsetExists($offset) {
        if (!is_string($offset)) {
            return parent::offsetExists($offset);
        } else {
            $pos = strpos($this->buffer, $offset);

            if (!empty($pos) && $pos >= 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Will replace the char in position offset to a blank space
     * 
     * @param integer $offset
    */
    public function offsetUnset($offset) {
        if (is_integer($offset)) {
            parent::offsetUnset($offset);
        } else {
            # string replace as empty string
            $this->buffer = str_replace($offset, "", $this->buffer);
        }
    }

    /**
     * The implemented ``CharAt`` function.
     * 
     * @param integer $offset
     * @return string
    */
    public function offsetGet($offset) {
        if (is_integer($offset)) {
            return $this->buffer{$offset};
        } else {
            return strpos($this->buffer, $offset);
        }
    }

    #endregion

    /** 
     * @return ScriptBuilder
    */
    public static function LoadFile($path) {
        $script = file_get_contents($path);
        return new ScriptBuilder($script);
    }
}
