<?php

Imports("Debugger.tableView");

/**
 * Parses the request header into resource, headers and security code
 * (解析http请求头部)
 *
 * @param string $request The request
 * @return array Array containing the resource, headers and security code
 */
function parseRequestHeader($request) {
    $headers = [];

    foreach (explode("\r\n", $request) as $line) {
        if (strpos($line, ': ') !== false) {
            list($key, $value) = explode(': ', $line);

            $headers[trim($key)] = trim($value);
        } else if (!empty($line)) {
            $tokens = explode(" ", $line);

            $headers["Method"] = $tokens[0];
            $headers["Url"] = $tokens[1];
            $headers["Http Version"] = $tokens[2];
        }
    }

    return $headers;
}

class httpSocket {

    private $address;
    private $port;
    private $socket;
    private $processor;

    /** 
     * @param string $address The ip address of this socket server
     * @param callable $processor The http request processor, by default is returns nothing
     * @param integer $port The listen port of the http socket
    */
    public function __construct($address, $processor = NULL, $port = 85) {
        $this->address   = $address;
        $this->port      = $port;
        $this->processor = $processor;

        if (!$this->processor) {
            // process the request and then returns the result string
            $this->processor = function($request) {
                // do nothing
                return "";
            };

            console::log("Empty request processor...");
        }

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
    */
    public function Run() {
        do {
            $this->doAccept();
        } while(true);
    }

    private function doAccept() {
        // 它接收连接请求并调用一个子连接Socket来处理客户端和服务器间的信息
        $msgsock = socket_accept($this->socket); # or die(self::socketErr("socket_accept"));
        
        // 读取客户端数据
        console::log("Read client data");

        // socket_read函数会一直读取客户端数据,直到遇见\n,\t或者\0字符.PHP脚本把这写字符看做是输入的结束符.
        $buf = socket_read($msgsock, 8192);
        $headers = parseRequestHeader($buf);

        console::dump($headers, "Received msg: ");
        
        // 数据传送 向客户端写入返回结果
        $process = $this->processor;
        $msg = $process($headers);

        socket_write($msgsock, $msg, strlen($msg)); # or die(self::socketErr("socket_write"));
        // 一旦输出被返回到客户端,父/子socket都应通过socket_close($msgsock)函数来终止
        socket_close($msgsock);
    }
}