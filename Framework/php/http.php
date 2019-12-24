<?php

imports("php.URL");

/**
 * Parses the request header into resource, headers and security code
 * (解析http请求头部)
 *
 * @param string $request The request
 * @return array Array containing the resource, headers and security code
 */
function parseRequestHeader($request) {
    $headers = ["Method" => "NA", "Url" => [], "Url Raw" => "/", "Http Version" => "NA"];

    foreach (explode("\r\n", $request) as $line) {
        if (strpos($line, ': ') !== false) {
            list($key, $value) = explode(': ', $line);

            $headers[trim($key)] = trim($value);
        } else if (!empty($line)) {
            $tokens = explode(" ", $line);

            $headers["Method"] = $tokens[0];
            $headers["Url"] = URL::mb_parse_url($tokens[1], true);
            $headers["Url Raw"] = $tokens[1];
            $headers["Http Version"] = $tokens[2];
        }
    }

    return $headers;
}

function pasteResponseHeader($headers) {
    $headers = implode("\r\n", $headers) . "\r\n\r\n";
    return $headers;
}

function ApachePageNotFound($url) {
    return "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
    <html><head>
    <title>404 Not Found</title>
    </head><body>
    <h1>Not Found</h1>
    <p>The requested URL {$url} was not found on this server.</p>
    </body></html>";
}

/**
 * 这个模块主要是应用于后台任务调用，需要使用root账号在后台运行这个服务器模块
 * 然后daemon用户所运行的apache接受请求之后，在通过http请求这个模块所运行的服务
 * 来执行后台任务
*/
class httpSocket {

    private $address;
    private $port;
    private $socket;

    /** 
     * The backend app
    */
    private $processor;

    /** 
     * 是否关闭当前的这个http模块进程
     * 
     * @var bool
    */
    private $shutdown;

    /** 
     * @param string $address The ip address of this socket server    
     * @param integer $port The listen port of the http socket
    */
    public function __construct($address, $port = 85) {
        $this->address   = $address;
        $this->port      = $port;
        $this->shutdown  = false;

        # 确保在连接客户端时不会超时
        set_time_limit(0);

        # 创建一个SOCKET 
        # AF_INET=是ipv4 如果用ipv6，则参数为 AF_INET6
        # SOCK_STREAM为socket的tcp类型，如果是UDP则使用SOCK_DGRAM
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die(self::socketErr("socket_create"));
        # 阻塞模式
        socket_set_block($sock) or die(self::socketErr("socket_set_block"));
        # 绑定到socket端口
        $result = socket_bind($sock, $address, $port) or die(self::socketErr("socket_bind"));
        # 开始监听
        $result = socket_listen($sock, 4) or die(self::socketErr("socket_listen"));

        $this->socket = $sock;
        $this->welcomeMessage();
    }

    private function welcomeMessage() {
        console::log("OK\nBinding the socket on {$this->address}:{$this->port} ... \n");
        console::log("OK\nNow ready to accept connections.\n");
        console::log("Listening on the socket ... \n");
    }

    private static function socketErr($trace) {
        return "$trace(): " . socket_strerror(socket_last_error()) . "\n";
    }

    /** 
     * Run http socket server daemon
     * 
     * @param object $app The http request processor
    */
    public function Run($app) {
        $this->processor = $app;

        do {
            $this->doAccept();
        } while(!$this->shutdown);

        console::log("Http task server signaled shutdown...\n\nBye bye.");
    }

    private function doAccept() {
        // 它接收连接请求并调用一个子连接Socket来处理客户端和服务器间的信息
        $msgsock = socket_accept($this->socket); # or die(self::socketErr("socket_accept"));
        
        // 读取客户端数据
        console::log("Read client data");

        # 2019-05-13 force disconnect socket connection, fix the thread block bugs 
        # that comes from google chrome.
        $timeout = ['sec' => 1, 'usec' => 0];
        socket_set_option($msgsock, SOL_SOCKET, SO_RCVTIMEO, $timeout);
        socket_set_option($msgsock, SOL_SOCKET, SO_SNDTIMEO, $timeout);

        // socket_read函数会一直读取客户端数据,直到遇见\n,\t或者\0字符.PHP脚本把这写字符看做是输入的结束符.
        $buf = @socket_read($msgsock, 8192);
        $headers = parseRequestHeader($buf);
        $origin = "";
        
        socket_getpeername($msgsock, $origin);

        if (IS_CLI && FRAMEWORK_DEBUG) {
            console::table($headers, ["headers" => "text"]);
        } else {
            echo $buf;
        }        
                
        $headers["remote"] = $origin;
        $isBrowser = array_key_exists("User-Agent", $headers);
        $msg = $this->response(
            $this->doProcess($headers), 
            $headers["Url Raw"], 
            $isBrowser
        );

        # 如果是google chrome浏览器，可能会在这里因为keep alive而占用很长的时间
        # 导致整个应用服务器无响应
        @socket_write($msgsock, $msg, strlen($msg)); # or die(self::socketErr("socket_write"));
        // 一旦输出被返回到客户端,父/子socket都应通过socket_close($msgsock)函数来终止
        @socket_close($msgsock);
    }

    private function doProcess($request) {
        // 数据传送 向客户端写入返回结果
        // url请求需要在processor函数之中自己解析，在这里不可以覆盖掉全局的$_GET变量
        // 因为这个$_GET变量可能是会在好几个并行的process处理过程之中共享的
        // 在process处理过程之中也不可以覆盖掉$_GET全局变量
        if ($this->checkShutdown($request)) {
            $this->shutdown = true;
            $msg = "Ok! Bye bye.";
        } else {
            $args = $request["Url"];
            $args = Utils::ReadValue($args, "query", []);

            # use the common router module for run backend app
            $msg  = Router::HandleRequest($this->processor, $args);
        }

        return $msg;
    }

    private function response($content, $url, $isBrowser) {
        $headers = $isBrowser ? "HTTP1.1" : "HTTP/1.1";
        $headers = [
            $content === 404 ? "$headers 404 Not Found" : "$headers 200 OK",
            "Connection: Close",
            "Content-Type: text/html",
            "Transfer-Encoding: chunked"
        ];
        $headers = pasteResponseHeader($headers);
        
        if ($content === 404) {
            return $headers . ApachePageNotFound($url);
        } else {
            return $headers . $content;
        }
    }

    /** 
     * @return bool 返回一个逻辑值来指示是否是关闭当前的服务器的请求
    */
    private function checkShutdown($request) {
        $args = Utils::ReadValue($request["Url"], "query");

        if (empty($args) || !array_key_exists("action", $args)) {
            return false;
        } else if ($args["action"] == "shutdown" && $request["remote"] == "127.0.0.1") {
            return true;
        } else {
            return false;
        }
    }
}