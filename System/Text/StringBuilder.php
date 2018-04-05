<?php

class StringBuilder {

    private $buffer;
    private $newLine;

    public function __construct($string = NULL, $newLine = "\n") {
        if (!$string) {
            $this->buffer = "";
        } else {
            $this->buffer = $string;
        }

        $this->newLine = $newLine;
    }

    public function AppendLine($str = "") {
        return $this->Append($str . $this->newLine);
    }

    public function Append($str) {
        $this->buffer = $this->buffer . $str;
        return $this;
    }

    public function Clear() {
        $this->buffer = "";
    }

    public function ToString() {
        return $this->buffer;
    }
}
?>