<?php

class StringBuilder {

    private $buffer;

    public function __construct($string = NULL) {
        if (!$string) {
            $this->buffer = "";
        } else {
            $this->buffer = $string;
        }
    }

    public function AppendLine($str = "") {
        return $this->Append($str . "\n");
    }

    public function Append($str) {
        $this->buffer = $this->buffer . $str;
        return $this;
    }

    public function ToString() {
        return $this->buffer;
    }
}
?>