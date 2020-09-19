<?php

namespace MVC\Controller {

    class appCaller {

        /**
         * get parameter names
         * 
         * @param controller $controller web app controller object
         * 
         * @return string[] the parameter value array
        */
        public static function getAppArguments(controller $controller) {
            $reflectionMethod = $controller->app_logic;
            $params = $reflectionMethod->getParameters();
            $fire_args = [];

            foreach($params AS $arg) {
                if($_REQUEST[$arg->name]) {
                    $fire_args[$arg->name] = $_REQUEST[$arg->name];
                } else {
                    $fire_args[$arg->name] = null;
                }                                
            }

            
        }
    }
}

