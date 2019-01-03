<?php

Imports("php.DocComment");

# 当前所支持的控制器函数的程序注解标签
# 
# @access 用户权限访问控制，后面跟着用户的分组名称，*表示不进行用户身份检查
# @uses   定义当前的控制器函数所返回给客户端的数据类型，默认为html文档
# @rate   用户对当前的控制器函数所指定的服务器资源的访问量的控制，即控制用户在某一段时间长度内的访问请求次数，
#         一段时间内超过指定的访问次数服务器将会返回429错误代码拒绝用户的访问
# @origin 控制请求的来源，即服务器的跨域请求配置，*表示当前的服务器资源不限制跨域请求

/**
 * php.NET Access controller model
 * 
 * 需要重写下面的几个方法才可以正常工作
 * 
 * + ``abstract public function accessControl();``
 * + ``public function Redirect($code) {}``
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
     * @var \PHP\ControllerDoc
    */
    protected $docComment;

    /**
     * 当前的这个控制器所指向的服务器资源的唯一标记，格式为：
     * 
     *   ``scriptName/appName``
     * 
     * @var string
    */
    var $ref;

    /**
     * 指示控制器是否已经发送了content-type http头信息
     * 
     * @var boolean
    */
    private static $hasSendContentType = false;

    /**
     * Get php function document comment 
     * 
     * Get php function document comment parsed object 
     * for current controller.
     * 
     * @return \PHP\ControllerDoc
    */
    public function getDocComment() {
        return $this->docComment;
    }

    /**
     * The controller access level, `*` means everyone!
    */
    public function getAccessLevel() {
        return $this->getTagValue("access");
    }

    /**
     * 获取对当前的服务器资源的访问量限制的阈值
    */
    public function getRateLimits() {
        return $this->getTagValue("rate");
    }

    /**
     * 获取当前的控制器所接受的访问的ip地址列表
     * ``#localhost``标记会被自动转换为本地服务器
     * 的ip地址列表
     * 
     * ip地址列表之中的地址使用``|``符号进行分隔
    */
    public function getAccepts() {
        $iplist = $this->getTagValue("accept");

        if ($iplist) {
            // 仅限于来自于这个ip列表的数据请求
            // 来自于其他的ip地址的数据请求一律拒绝
            $iplist  = explode("|", $iplist);
            $accepts = [];

            foreach($iplist as $tagIP) {
                if (strtolower($tagIP) === "@localhost") {
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

    /**
     * 获取跨域访问控制
    */
    public function getAccessAllowOrigin() {
        return $this->getTagValue("origin");
    }

    /**
     * 获取当前的控制器函数的注释文档里面的某一个标签的说明文本
    */
    public function getTagDescription($tag) {
        return $this->readTagImpl($tag, "description");
    }

    /**
     * 如果tag或者tag之中不存在所给定的key，这两种情况都会返回空字符串
    */
    private function readTagImpl($tag, $key) {
        if (empty($this->docComment)) {
            return "";
        }

        $tag = Utils::ReadValue($this->docComment->tags, $tag);

        if (!empty($tag)) {
            return $tag[$key];
        } else {
            return "";
        }
    }

    public function getTagValue($tag) {
        return $this->readTagImpl($tag, "value");
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
        return $this->getTagValue("uses");
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
        self::$hasSendContentType = true;
        
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
            case "text":
                # 返回的是存文本内容
                header("Content-Type: text/plain");
                break;

            default:
                # DO NOTHING
                self::$hasSendContentType = false;
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
        $this->appObj = $app;

        /*
        # Add method dynamics not working
        $controller = $this;

        $this->appObj->{"success"} = function($message) use ($controller) {
            $controller->success($message);
        };
        $this->appObj->{"error"} = function($message, $errCode = 500) use ($controller) {
            $controller->error($message, $errCode);
        };
        */

        // 先检查目标方法是否存在于逻辑层之中
        if (!method_exists($app, $page = Router::getApp())) {
            # 如果是调试模式下，则可能是调试器调用
            if (APP_DEBUG && dotnetDebugger::IsDebuggerApiCalls()) {
                # 处理调试器调用请求
                dotnetDebugger::handleApiCalls();
                // 在这里需要提前结束脚本的执行
                // 否则下面的反射调用代码会出错
                exit(0);
            } else {
                # 其他的情况目前都被判定为404错误
                # 不存在，则抛出404
                $this->handleNotFound();
            }
        } else {
            $this->ref = DotNetRegistry::GetInitialScriptName();
            $this->ref = "{$this->ref}/$page";

            $msg = "Reflects on web app => <strong><code>{$this->ref}</code></strong>";
            debugView::LogEvent($msg);
        }

        if (!is_object($app)) {
            throw new Error("App should be a class object!");
        } else {
            $reflector = new ReflectionClass(get_class($app));
            $appName   = Router::getApp();

            $this->reflection = $reflector;
            $this->app_logic  = $reflector->getMethod($appName);
            $this->docComment = $this->app_logic->getDocComment();
            $this->docComment = \PHP\ControllerDoc::ParseControllerDoc($this->docComment);
        }

        return $this;
    }
    
    /**
     * 处理web请求
     * 
     * 如果需要显示调试窗口，还需要将该控制器标记为view类型
    */
    public function handleRequest() {
        $origin = $this->getAccessAllowOrigin();
        $isView = strtolower($this->getUsage()) === "view";

        if (APP_DEBUG && $isView) {
            # 2019-1-3 因为http头部必须要在content之前输出才有效
            # 所以对于当前的session的调试器信息输出必须要
            # 发生在处理用户请求之前来完成
            $name = DEBUG_SESSION;
            $guid = DEBUG_GUID;
            header("Set-Cookie: $name=$guid");
        }
        if (!Strings::Empty($origin)) {
            header("Access-Control-Allow-Origin: $origin");
        }

        # 在这里执行用户的控制器函数
        $bench = new \Ubench();
        $code  = $bench->run(function($controller) {
            $controller->appObj->{Router::getApp()}();
        }, $this);       

        debugView::LogEvent("[Finish] Handle user request");
        debugView::AddItem("benchmark.exec", $bench->getTime(true));

        # 在末尾输出调试信息？
        # 只对view类型api调用的有效
        
		if (APP_DEBUG && $isView) {
            # 在这里自动添加结束标记
            debugView::LogEvent("--- App Exit ---");
			debugView::Display();
        } else {
            // 假设不是view类型的控制器的话，则在这里可能是api类型的调用
            // 需要在这里写入调试器session信息
            if (APP_DEBUG) {
                dotnet::$debugger->WriteDebugSession();
            }
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
     * 处理所请求的资源找不到的错误，默认为抛出404错误页面
     * 
     * > ##### 2019-1-3 
     * > 请注意，如果需要重写这个函数的话，会需要在处理完之后调用exit结束脚本的执行
     * > 否则控制器模块没有被提前结束的话，后续的反射调用函数会报错
    */
    public function handleNotFound() {
        $app = Router::getApp();
        $msg = "Web app `<strong>$app</strong>` is not available in this controller!";

        dotnet::PageNotFound($msg);
    }

    /**
     * 假若没有权限的话，会执行这个函数进行重定向
     * 这个函数默认是返回403错误页面
    */
    public function Redirect($code) {
        if ($code == 403) {
            dotnet::AccessDenied("Invalid credentials!");
        } else if ($code == 429) {
            dotnet::TooManyRequests("Too many request!");
        } else {
            dotnet::ThrowException("Unknown server error...");
        }
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
    public static function success($message) {
        header("HTTP/1.1 200 OK");
        header("Content-Type: application/json");

        echo dotnet::successMsg($message);

        if (APP_DEBUG) {
            dotnet::$debugger->WriteDebugSession();
        }

        exit(0);
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
    public static function error($message, $errCode = 1) {
        header("HTTP/1.1 200 OK");
        # 2018-10-11 不能够在这里设置500错误码，这个会导致
        # jquery的success参数回调判断失败，无法接受错误消息
        # header("HTTP/1.0 500 Internal Server Error");
        header("Content-Type: application/json");

        echo dotnet::errorMsg($message, $errCode);

        if (APP_DEBUG) {
            dotnet::$debugger->WriteDebugSession();
        }

        exit($errCode);
    }
}
?>