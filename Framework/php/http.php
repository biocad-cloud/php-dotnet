<?php

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
        }
    }

    return $headers;
}

class httpSocket {

    private $address;
    private $port;
    private $socket;

    public function __construct($address, $port = 85) {
        $this->address = $address;
        $this->port = $port;

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
}