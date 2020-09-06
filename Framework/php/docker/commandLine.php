<?php

namespace docker;

class CommandLine {

    /**
     * build commandline
     * 
     * a helper function for build a command line string
     * for run docker commands.
    */
    public static function commandlineArgs($commandName, $args = NULL) {
        if (empty($args)) {
            $arguments = "";
        } else {
            $arguments = [];

            foreach($args as $name => $opts) {
                $arguments[] = self::argumentToken($name, $opts);
            }

            $arguments = implode(" ", $arguments);
        }        
    
        return "docker $commandName $arguments";
    }

    private static function argumentToken($argName, $opts) {
        if (empty($opts)) {
            return "";
        } else if (is_array($opts)) {
            $tokens = [];

            foreach($opts as $val) {
                $tokens[] = "$argName \"$val\"";
            }

            return implode(" ", $tokens);
        } else if (is_bool($opts)) {
            if ($opts == TRUE) {
                return $argName;
            } else {
                return "";
            }
        } else {
            return "$argName \"$opts\"";
        }
    }

    /**
     * create volume binding
     * 
     * @param array $v a list object should in data structure like:
     *     ``[anyname => [host => ..., virtual => ...]]``, the host
     *     path can be relative path, but the virtual path must be
     *     in absolute format!
     * 
    */
    public static function volumeBind($v) {
        if (empty($v)) {
            "";
        } else {
            $binding = [];
            
            foreach($v as $mapping) {                
                $localhost = $mapping["host"];

                if (!($localhost == "$(which docker)" || $localhost == "/var/run/docker.sock")) {
                    $localhost = \realpath($localhost);
                }

                $virtual = $mapping["virtual"];
                $binding[] = "$localhost:$virtual";
            }

            return $binding;
        }
    }
}