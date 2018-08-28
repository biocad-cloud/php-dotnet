<?php

Imports("php.DocComment");

/**
 * php.NET Access controller model
*/
abstract class controller {

    /**
     * Web app应用的逻辑实现，这个变量应该是一个class object来的
     * 
     * @var object
    */
    protected $appObj;
    /**
     * (ReflectionClass) 对Web app应用的逻辑层的反射器
     * 
     * @var ReflectionClass
    */
    protected $reflection;
    /**
     * 对web app的逻辑实现方法
     * 
     * @var ReflectionMethod
    */    
    protected $app_logic;
    /**
     * 编写在当前的这个控制器函数之上的注释文档的解析结果
     * 
     * @var DocComment
    */
    protected $docComment;

    /**
     * Get php function document comment 
     * 
     * Get php function document comment parsed object 
     * for current controller.
     * 
     * @return DocComment
    */
    public function getDocComment() {
        return $this->docComment;
    }

    /**
     * The controller access level, `*` means everyone!
    */
    public function getAccessLevel() {
        return $this->getTagDescription("access");
    }

    /**
     * 获取当前的控制器函数的注释文档里面的某一个标签的说明文本
    */
    public function getTagDescription($tag) {
        if (!empty($this->docComment)) {
            $tag = Utils::ReadValue($this->docComment->tags, $tag);

            if (!empty($tag)) {
                return $tag["description"];
            } else {
                return "";
            }
        } else {
            return "";
        }
    }

    /**
     * Get controller api return type
     * 
     * 使用``@uses``标签来标记当前的这个控制器函数的用途：
     * 数据api，文档视图还是soap api？
     * 这个标签的使用将会影响http header之中的``content-type``的内容。
     * 
     * @return string Returns the controller api return type:
     * 
     *    - `api`    This controller is a rest API, and returns a json string, not html document.
     *    - `view`   This controller is a html view api, and returns the html document.
     *    - `soap`   This controller is a soap API, and returns a XML document.
     *    - `router` This controller is a browser redirect controls, browser will be redirect to 
     *               another location.
     * 
    */
    public function getUsage() {
        return $this->getTagDescription("uses");
    }

    /**
     * 当前的服务器资源是否具有访问量的限制？
     * 
     * 使用``@rate``标签来注释当前的控制器函数的访问量限制阈值
     * 
     * @return boolean 返回一个逻辑值来表示当前的服务器资源是否具有访问量的限制？
    */
    public function HasRateLimits() {
        return (!empty($this->docComment)) && array_key_exists("rate", $this->docComment->tags);
    }

    /**
     * 查看当前的这个控制器是否所有人都可以访问？
     * 
     * 如果当前的这个控制器函数的``@access``标记的值是``*``
     * 的话，说明当前的这个控制器是不需要进行任何身份验证，
     * 所与人都可以公开访问的api
     * 
     * @return boolean true表示可以被所有人访问，false表示需要进行身份凭证验证
    */
    public function AccessByEveryOne() {
        return $this->getAccessLevel() == "*";
    }

    /**
     * 这个可以在访问控制器之中应用，这个函数只对定义了@uses标签的控制器有效
     * 如果控制器函数没有定义@uses标签，则不会写入任何content-type的数据
    */
    public function sendContentType() {        
        switch(strtolower($this->getUsage())) {
            case "api":
                header("Content-Type: application/json");
                break;
            case "view":
                header("Content-Type: text/html");
                break;
            case "soap":
                header("Content-Type: text/xml");
                break;
            case "router":
                # 浏览器重定向这里怎么表述？
                break;

            default:
                # DO NOTHING
        }
    }

    /**
     * 构建一个对web app的访问控制器
     * 
     * 将这个控制器对象挂载到目标Web应用程序逻辑层之上，这个函数在完成挂载操作
     * 之后会返回控制器程序自己本身
     * 
     * @param object $app 应该是一个class，如果不是，则会抛出错误
     * 
     * @return controller 函数返回这个控制器本身
    */
    public function Hook($app) {
        $this->appObj  = $app;

        /*
        $this->appObj->success = function($message) {
            $this->success($message);
        };
        $this->appObj->error = function($message, $errCode = 1) {
            $this->error($message, $errCode);
        }*/

        // 先检查目标方法是否存在于逻辑层之中
        if (!method_exists($app, $page = Router::getApp())) {
            # 不存在，则抛出404
            $message = "Web app `<strong>$page</strong>` is not available in this controller!";
			dotnet::PageNotFound($message);
        } else {
            debugView::LogEvent("Reflects on web app => $page");
        }

        if (!is_object($app)) {
            throw new Error("App should be a class object!");
        } else {
            $reflector = new ReflectionClass(get_class($app));

            $this->reflection = $reflector;
            $this->app_logic  = $reflector->getMethod(Router::getApp());   
            $this->docComment = $this->app_logic->getDocComment();   
            $this->docComment = DocComment::Parse($this->docComment);
        }

        return $this;
    }
    
    /**
     * 处理web请求
     * 
     * 如果需要显示调试窗口，还需要将该控制器标记为view类型
    */
    public function handleRequest() {       
        # 在这里执行用户的控制器函数
        $bench = new \Ubench();
        $code  = $bench->run(function($controller) {
            $controller->appObj->{Router::getApp()}();
        }, $this);       

        debugView::LogEvent("[Finish] Handle user request");
        debugView::AddItem("benchmark.exec", $bench->getTime(true));

        # 在末尾输出调试信息？
        # 只对view类型api调用的有效
		if (APP_DEBUG && strtolower($this->getUsage()) == "view") {
            # 在这里自动添加结束标记
            debugView::LogEvent("--- App Exit ---");
			debugView::Display();
        }
        
        exit(0);
    }

    #region "Access control overrides"

    /**
     * 函数返回一个逻辑值，表明当前的访问是否具有权限，如果这个函数返回False，那么
     * web服务器将会默认响应403，访问被拒绝
     * 
     * @return boolean 当前的访问权限是否验证成功？
    */
    abstract public function accessControl();

    /**
     * 对当前用户访问当前的这个服务器资源的访问量控制的控制器函数
     * 
     * @return boolean 当前用户对当前的这个服务器资源的访问量是否超过了配额？
     *      如果这个函数返回true，则表示已经超过了配额限制，则会拒绝访问
     *      如果这个函数返回false，则表示当前的用户访问正常
    */
    public function Restrictions() {
        # 可以重载这个控制器函数来实现对某一个服务器资源的访问量的限制
        return false;
    }

    /**
     * 假若没有权限的话，会执行这个函数进行重定向
     * 这个函数默认是返回403错误页面
    */
    public function Redirect() {
        dotnet::AccessDenied("Invalid credentials!");
    }

    #region

    /**
     * 在完成了这个函数的调用之后，服务器将会返回成功代码
     * 并退出当前的脚本执行状态
     * 
     * @param string $message 需要通过json进行传递的消息文本
     * 
     * @return void
    */
    public function success($message) {
        header("HTTP/1.1 200 OK");
        header("Content-Type: application/json");

        echo dotnet::successMsg($message);
        exit();
    }

    /**
     * 在完成了这个函数的调用之后，服务器将会返回错误代码
     * 并退出当前的脚本执行状态
     * 
     * @param string $message 需要通过json进行传递的消息文本
     * @param integer $errCode 错误代码，默认为1
     * 
     * @return void
    */
    public function error($message, $errCode = 1) {
        header("HTTP/1.0 500 Internal Server Error");
        header("Content-Type: application/json");

        echo dotnet::errorMsg($message, $errCode);
        exit($errCode);
    }
}
?>