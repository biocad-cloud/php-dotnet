<?php

/**
 * Represents a mutable string of characters.
*/
class StringBuilder {

    /**
     * @var string 
    */
    private $buffer;

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
}
?>