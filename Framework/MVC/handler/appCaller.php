<?php

namespace MVC\Controller {

    class appCaller {

        /**
         * call web app controller in a dynamics way
         * 
         * @param object $appObj the web app object instance
         * @param string $app the app name(function name)
        */
        public static function doCall($appObj, $app, $strict = false) {           
            $reflectionMethod = (new \ReflectionClass(get_class($appObj)))->getMethod($app);
            $params           = $reflectionMethod->getParameters();
            $fire_args        = [];

            foreach($params as $arg) {
                if (array_key_exists($arg->name, $_REQUEST)) {
                    $fire_args[] = $_REQUEST[$arg->name];
                } else if ($arg->isOptional()) {
                    $fire_args[] = $arg->getDefaultValue();
                } else if ($strict) {
                    \dotnet::BadRequest("missing the required parameter '{$arg->name}' in your http request!");
                } else {
                    $fire_args[] = null;
                }               
            }

            return $reflectionMethod->invokeArgs($appObj, $fire_args);
        }
    }
}

