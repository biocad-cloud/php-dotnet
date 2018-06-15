<?php

Imports("Microsoft.VisualBasic.CommandLine.CommandLine");
Imports("Microsoft.VisualBasic.Extensions.StringHelpers");
Imports("Microsoft.VisualBasic.Strings");
Imports("php.Utils");

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

    public static function ParseCLIArgvs() {
        $name      = $argv[1];
        $arguments = [];

        for ($i = 1; $i < count($argv); $i++) {
            $term = $argv[$i];

            if (Strings::InStr($term, "=")) {
                $term = StringHelpers::GetTagValue($term, "=");
                list($key, $value) = Utils::Tuple($term);
            } else {

                // is a logical boolean flag, and it is 
                // true if it is presented.
                list($key, $value) = [$term, true]; 
            }            

            $arguments[$key] = $value;
        }

        return new CommandLine($name, $arguments);
    }
}

?>