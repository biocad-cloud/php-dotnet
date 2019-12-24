<?php

imports("System.Text.StringBuilder");

/**
 * 在StringBuilder的基础之上增加了更加方便的字符串替换方法
*/
class ScriptBuilder extends StringBuilder implements ArrayAccess {

    /** 
     * ``[key => value]``字典
     * 
     * @var array
    */
    var $vars = [];

    /**
     * @param string $string The initialize string buffer
    */
    public function __construct($string = NULL, $newLine = PHP_EOL) {
        parent::__construct($string, $newLine);
    }

    /**
     * Converts the value of a ``ScriptBuilder`` to a String.
     * 
     * 将当前的这个``StringBuilder``对象之中的字符串缓冲字符串输出
     * 
     * @return string 最终所拼接出来的文本字符串数据
    */
    public function ToString() {
        $buffer = $this->buffer;

        foreach($this->vars as $offset => $replaceValue) {
            if ($offset[0] !== "@") {
                $symbol = "@" . $offset; 
            } else {
                $symbol = $offset;
            }

            $buffer = str_replace($symbol, $replaceValue, $buffer);
        }

        return $buffer;
    }

    #region "implements ArrayAccess: charAt index function/symbol replacement"

    # 在这里实际上就是将offset和对应的value添加进入vars字典之中

    /**
     * Symbol expression: @symbol
     * 
     * @param integer $offset Char at index/symbol
     * @param string $value A char(string with one character)
    */
    public function offsetSet($offset, $value) {
        if (is_string($offset)) {
            # push variables
            $this->vars[$offset] = $value;
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
            return array_key_exists($offset, $this->vars);
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
            unset($this->vars[$offset]);
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
            return Utils::ReadValue($this->vars, $offset);
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
