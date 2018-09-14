<?php

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

    public function Clear() {
        $this->buffer = "";
    }

    /**
     * @return string
    */
    public function ToString() {
        return $this->buffer;
    }
}
?>