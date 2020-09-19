<?php

namespace MVC\Controller {

    class appCaller {

        /**
         * get parameter names
         * 
         * @param object $appObj the web app object instance
         * @param string $app the app name(function name)
        */
        public static function getAppArguments($appObj, $app) {
            $reflectionMethod =  new \ReflectionMethod(new \ReflectionClass(get_class($appObj)), $app);
            $params = $reflectionMethod->getParameters();
            $fire_args = [];

            foreach($params as $arg) {
                echo var_dump($arg);
                
                if ($_REQUEST[$arg->name]) {
                    $fire_args[$arg->name]=$_REQUEST[$arg->name];
                } else {
                    $fire_args[$arg->name]=null;
                }
            }
        }
    }
}

