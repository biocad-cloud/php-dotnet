<?php

Imports("Microsoft.VisualBasic.CommandLine.CommandLine");

class CommandLineParser {

    /**
     * 
     * @return CommandLine
    */
    public static function ParseCLIInput($cli) {
        $name      = "";
        $arguments = [];


        return new CommandLine($name, $arguments);
    }
}

?>