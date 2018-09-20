<?php

#  +--------+  1. Send Sec-WebSocket-Key                 +--------+
#  |        | -----------------------------------------> |        |
#  |        |  2. Return encrypted Sec-WebSocket-Accept  |        |
#  | client | <----------------------------------------- | server |
#  |        |  3. Verify locally                         |        |
#  |        | -----------------------------------------> |        |
#  +--------+                                            +--------+

# GET /chat HTTP/1.1
# Host: server.example.com
# Upgrade: websocket
# Connection: Upgrade
# Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==
# Origin: http://example.com
# Sec-WebSocket-Protocol: chat, superchat
# Sec-WebSocket-Version: 13

# demo code
#
# (new WebSocket("localhost", "258EAFA5-E914-47DA-95CA-C5AB0DC85B11"))
#     ->createServer(function($data, $write) {
#         $write(Utils::Now() . ": " . $data);
#     })
#     ->listen(5005);

Imports("System.Text.StringBuilder");

class WebSocket {

    /**
     * 连接 server 的 client
     * 
     * @var resource 
    */
    var $master;
    /**
     * 不同状态的 socket 管理
     * 
     * @var array
    */
    var $sockets = []; 
    /**
     * 判断是否握手
     * 
     * @var boolean
    */
    var $handshake = false;
    /**
     * @var string
    */
    var $mask;
    var $dataResponse;
    /**
     * @var string
    */
    var $address;
    /**
     * @var integer
    */
    var $bufferSize;

    function __construct($address = "localhost", 
                         $mask    = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", 
                         $bufSize = 2048) {

        $this->address    = $address;
        $this->mask       = $mask;
        $this->bufferSize = $bufSize;
    }

    private function loopTask() {
        // 自动选择来消息的 socket 如果是握手 自动选择主机
        $write  = NULL;
        $except = NULL;
        socket_select($this->sockets, $write, $except, NULL);

        foreach ($this->sockets as $socket) {
            //连接主机的 client 
            if ($socket == $this->master) {
                $this->tryConnect();
            } else {
                if (!$this->handleRequest($socket)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function tryConnect() {
        $client = socket_accept($this->master);

        if ($client < 0) {
            // debug
            echo "socket_accept() failed";
            return;
        } else {
            //connect($client);
            array_push($this->sockets, $client);
            echo "connect client\n";
        }
    }

	private function disConnect($socket) {
		$index = array_search($socket, $this->sockets);
        socket_close($socket);
        
        echo ($socket . " DISCONNECTED!\n");
        
		if ($index >= 0){
			array_splice($this->sockets, $index, 1); 
        }
        
        return true;
	}

    private function handleRequest($socket) {
        if(@socket_recv($socket, $buffer, 2048, 0) == 0) {
            return $this->disConnect($socket);
        }

        if (!$this->handshake) {
            // 如果没有握手，先握手回应
            $this->doHandShake($socket, $buffer);
            echo "shakeHands\n";
        } else {
            // 如果已经握手，直接接受数据，并处理
            $buffer  = $this->decode($buffer);
            $handles = $this->dataResponse;
            $write   = function($data) use ($socket) {
                socket_write($socket, $data, strlen($data));
            };
            $handles($buffer, $write);

            echo "send file\n";
        }

        return true;
    }

    /**
     * @param callable ($buffer, $write) => void
     * 
     * @return WebSocket
    */
    public function createServer($dataResponse) {
        $this->dataResponse = $dataResponse;
        return $this;
    }

    /**
     * 启动这个WebSocket服务器
     * 
     * @param integer $port 所监听的端口
    */
    public function listen($port) {
        // 建立一个 socket 套接字
        $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)   
            or die("socket_create() failed");
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1)  
            or die("socket_option() failed");
        socket_bind($this->master, $this->address, $port)                    
            or die("socket_bind() failed");
        socket_listen($this->master, 2)                               
            or die("socket_listen() failed");

        $this->sockets[] = $this->master;

        // debug
        echo("Master socket: {$this->master}\n");

        while(true) {
            if (!$this->loopTask()) {
                break;
            }
        }
    }

    /**
     * 解析数据帧 
    */ 
    private function decode($buffer) {
        $len = $masks = $data = $decoded = null;
        $len = ord($buffer[1]) & 127;

        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        } else if ($len === 127) {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        } else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }

        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }
        
        return $decoded;
    }

    private static function response($acceptKey) {
        return (new StringBuilder("", "\r\n"))
            ->AppendLine("HTTP/1.1 101 Switching Protocols")
            ->AppendLine("Upgrade: websocket")
            ->AppendLine("Connection: Upgrade")
            ->AppendLine("Sec-WebSocket-Accept: $acceptKey")
            ->AppendLine("")
            ->ToString();
    }

    /**
     * @param string $req 浏览器端发送过来的请求头
    */
    private function doHandShake($socket, $req) {
        // 获取加密key
        $acceptKey = $this->encry($req);
        $upgrade   = self::response($acceptKey);
    
        echo "$acceptKey\n";

        // 写入socket
        socket_write($socket, $upgrade, strlen($upgrade));
        // 标记握手已经成功，下次接受数据采用数据帧格式
        $this->handshake = true;
    }

    /**
     * @param string $req 浏览器端发送过来的请求头
     * 
     * @return string
    */
    private function encry($req) {
        $key = sha1(self::getKey($req) . $this->mask, true); 
        $key = base64_encode($key);
        return $key;
    }

    /**
     * @param string $req 浏览器端发送过来的请求头
     * 
     * @return string
    */
    private static function getKey($req) {
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $req, $match)) {
            $key = $match[1];

            echo $req . "\n\n";
            echo $key . "\n\n";
            return $key;
        } else {
            return null;
        }
    }
}