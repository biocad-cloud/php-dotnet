<?php

/**
 * 输入验证模块
*/
class controllerValidation {

    /**
     * @var controller
     * 
     * Web应用的控制器对象实例
    */
    var $controller;

    /**
     * @param controller $app
    */
    public function __construct($app) {
        $this->controller = $app;
    }

    /**
     * 主要是对url查询进行输入验证
    */
    public function doValidation() {
        $this->validateMethod();
        $this->validateArguments();
    }

    private function validateMethod() {
        // 在完成初始化之后，在这里检查http方法的合法性
        // 检查客户端所请求方法是否被允许
        $methods = $this->controller->getMethods();

        if (!in_array("*", $methods)) {
            # 如果控制器函数不允许任何方法的话，则在这里执行检查
            if (IS_GET) {
                if (!in_array("GET", $methods)) {
                    $this->handleInvalidMethod("GET");
                }
            } else {
                if (!in_array("POST", $methods)) {
                    $this->handleInvalidMethod("POST");
                }
            }
        }
    }

    private function validateArguments() {
        $require = $this->controller->getRequiredArguments();

        // 只有当出现了@require标签的时候才进行检查
        if (!empty($require)) {
            foreach($require as $arg) {
                $this->validateArgument($arg);
            }
        }
    }

    private function validateArgument($arg) {
        $arg = StringHelpers::GetTagValue($arg, "=", true);
        $val = Utils::ReadValue($_GET, $arg[0]);

        switch ($arg[1]) {
            case "i32":
                if (!StringHelpers::IsPattern($val, "\\d+")) {
                    $this->handleBadRequest($arg[0], "integer");
                }
                break;
            
            case "boolean":

                if (!($val == "true" || $val == "false" || $val == "1" || $val == "0")) {
                    $this->handleBadRequest($arg[0], "boolean");
                }
                break;

            default:
                # 默认是要求不为空
                if (Strings::Empty($val)) {
                    $this->handleBadRequest($arg[0], "none null");
                }
        }
    }

    /** 
     * 405
    */
    public function handleInvalidMethod($currentMethod) {
        $app = Router::getApp();
        $msg = "Web app `<strong>$app</strong>` is not allows <code>$currentMethod</code> method!";

        dotnet::InvalidHttpMethod($msg);
    }

    /**
     * 400
    */
    private function handleBadRequest($arg, $format) {
        $app = Router::getApp();
        $msg = "Web app `<strong>$app</strong>` have a required argument <code>$arg</code> which should be a $format value!";

        dotnet::BadRequest($msg);
    }
}