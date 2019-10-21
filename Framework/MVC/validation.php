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
        $this->validateAcceptIPWhitelist();
        $this->validateMethod();
        $this->validateArguments();
    }

    private function validateAcceptIPWhitelist() {
        $whitelist = $this->getAccepts();

        if (count($whitelist) > 0) {
            # 设置了白名单，则只允许白名单上面的ip地址来源的请求进行访问
            $user = Utils::UserIPAddress();

            if (!in_array($user, $whitelist)) {
                # 不在白名单中的请求都禁止访问当前的控制器
                dotnet::AccessDenied("You ip address '$user' is not in current server resource's ip whitelist...");
            }
        }
    }

    /**
     * 获取当前的控制器所接受的访问的ip地址列表
     * ``#localhost``标记会被自动转换为本地服务器
     * 的ip地址列表
     * 
     * ip地址列表之中的地址使用``|``符号进行分隔
    */
    public function getAccepts() {
        $iplist = $this->controller->getTagValue("accept");

        if ($iplist) {
            // 仅限于来自于这个ip列表的数据请求
            // 来自于其他的ip地址的数据请求一律拒绝
            $iplist  = explode("|", $iplist);
            $accepts = [];

            Imports("php.export");

            foreach($iplist as $tagIP) {
                if (strtolower($tagIP) === "localhost") {
                    foreach(localhost() as $ip) {
                        $accepts[] = $ip;
                    }
                } else {
                    $accepts[] = $tagIP;
                }
            }

            $iplist = $accepts;
        } else {
            // 没有做任何限制
            $iplist = [];
        }

        return $iplist;
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
        $argName = $arg[0];

        if (array_key_exists($argName, $_GET)) {
            $val = $_GET[$argName];
        } else if (IS_POST) {
            $val = Utils::ReadValue($_POST, $argName);
        } else {
            $val = null;
        }        

        switch ($arg[1]) {
            case "i32":
                if (!StringHelpers::IsPattern($val, "\\d+")) {
                    $this->handleBadRequest($argName, "integer");
                }
                break;
            
            case "boolean":

                if (!($val == "true" || $val == "false" || $val == "1" || $val == "0")) {
                    $this->handleBadRequest($argName, "boolean");
                }
                break;

            default:
                # 默认是要求不为空
                if (Strings::Empty($val)) {
                    $this->handleBadRequest($argName, "none null");
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