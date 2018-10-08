<?php

/**
 * Represents a mutable string of characters.
*/
class StringBuilder implements ArrayAccess {

    /**
     * 最终所生成的字符串数据
     * 
     * @var string 
    */
    protected $buffer;

    /**
     * @var string
    */
    private $newLine;

    /**
     * @param string $string The initialize string buffer
    */
    public function __construct($string = NULL, $newLine = PHP_EOL) {
        if (!$string) {
            $this->buffer = "";
        } else {
            $this->buffer = $string;
        }

        $this->newLine = $newLine;
    }

    /**
     * @param string $find
     * @param string $replaceValue
     * 
     * @return StringBuilder
    */
    public function Replace($find, $replaceValue) {
        $this->buffer = str_replace($find, $replaceValue, $this->buffer);
        return $this;
    }

    /**
     * Appends the default line terminator, or a copy of a specified string 
     * and the default line terminator, to the end of this instance.
     * 
     * @return StringBuilder
    */
    public function AppendLine($str = "") {
        return $this->Append($str . $this->newLine);
    }

    /**
     * Append the text into buffer without add new line
     * 
     * @param string $str The text for append into buffer.
     * 
     * @return StringBuilder return current object instance.
    */
    public function Append($str) {
        $this->buffer = $this->buffer . $str;
        return $this;
    }

    /**
     * Removes all characters from the current StringBuilder instance.
    */
    public function Clear() {
        $this->buffer = "";
    }

    /**
     * Converts the value of a StringBuilder to a String.
     * 
     * 将当前的这个StringBuilder对象之中的字符串缓冲字符串输出
     * 
     * @return string 最终所拼接出来的文本字符串数据
    */
    public function ToString() {
        return $this->buffer;
    }
    
    #region "implements ArrayAccess: charAt index function"

    /**
     * @param integer $offset Char at index
     * @param string $value A char(string with one character)
    */
    public function offsetSet($offset, $value) {
        $this->buffer{$offset} = $value;
    }

    /**
     * @param integer $offset index should be positive integer and in range of the string length.
     * 
     * @return boolean
    */
    public function offsetExists($offset) {
        if ($offset < 0 || $offset >= strlen($this->buffer)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Will replace the char in position offset to a blank space
     * 
     * @param integer $offset
    */
    public function offsetUnset($offset) {
        $this->buffer{$offset} = " ";
    }

    /**
     * The implemented ``CharAt`` function.
     * 
     * @param integer $offset
     * @return string
    */
    public function offsetGet($offset) {
        if ($this->offsetExists($offset)) {
            return $this->buffer{$offset};
        } else {
            return NULL;
        }
    }

    #endregion

}
?>