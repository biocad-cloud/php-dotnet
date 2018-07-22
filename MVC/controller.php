<?php

abstract class controller {

    /**
     * Web app应用的逻辑实现，这个变量应该是一个class object来的
    */
    protected $appObj;
    protected $reflection;

    /**
     * 构建一个对web app的访问控制器
     * 
     * @param object $app 应该是一个class
    */
    function __construct($app) {
        $this->appObj = $app;

        if (!is_object($app)) {
            throw new Error("App should be a class object!");
        } else {
            $this->reflection = new ReflectionClass();
        }
    }
    
    abstract public function accessControl() {

    }
}
?>