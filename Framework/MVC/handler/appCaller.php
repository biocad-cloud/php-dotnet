<?php

namespace MVC\Controller {

    imports("MVC.handler.payload");
    imports("MVC.request");
    
    class appCaller {

        /**
         * call web app controller in a dynamics way
         * 
         * @param object $appObj the web app object instance
         * @param string $app the app name(function name)
        */
        public static function doCall($appObj, $app, $strict = false) {
            return self::CallWithPayload($appObj, $app, new WebRequest(), $strict);
        }

        /**
         * Run app invoke with custom payload
         * 
         * @param object appObj the target app object instance
         * @param string app the method name
         * @param \MVC\Controller\payload payload a key-value pair list that store the parameter 
         *     data for run the given target method.
         * @param boolean strict work in strict mode?
        */
        public static function CallWithPayload($appObj, $app, $payload, $strict = false) {
            echo "enter";
            echo $app;
            $reflectionMethod = (new \ReflectionClass(get_class($appObj)))->getMethod($app);
            echo "arguments";
            $params           = $reflectionMethod->getParameters();
            $fire_args        = [];
    \breakpoint($payload);
            foreach($params as $arg) {
                if ($payload->_has($arg->name, false)) {
                    $fire_args[] = $payload->_get($arg->name);
                } else if ($arg->isOptional()) {
                    $fire_args[] = $arg->getDefaultValue();
                } else if ($strict) {
                    header("Content-Type: text/html");
                    \dotnet::BadRequest("missing the required parameter '{$arg->name}' in your http request!");
                } else {
                    $fire_args[] = null;
                }               
            }

            return $reflectionMethod->invokeArgs($appObj, $fire_args);
        }
    }
}