<?php

Imports("php.websocket");

class WsAppHandler implements PHP\WebSocket\SocketListener {

    /**
     * + message
     * + connect
     * + disconnect
     * + log
     * 
     * @var array
    */
    private $app;

    public function __construct() {
        $this->app = [];
    }

    /**
	 * Called when a client sends a message to the server.
	 *
	 * @param PHP\WebSocket\SocketServer $server The server instance
	 * @param PHP\WebSocket\SocketClient $client Client that sent the message
	 * @param string $message Sent message
	 */
	public function onMessageRecieved(
		PHP\WebSocket\SocketServer $server,
		PHP\WebSocket\SocketClient $sender,
		$message
	) {
        if (array_key_exists("message", $this->app)) {
            $app = $this->app["message"];
            $app($server, $sender, $message);
        }
    }

	/**
	 * Called when a new client connects to the server.
	 *
	 * @param PHP\WebSocket\SocketServer $server The server instance
	 * @param PHP\WebSocket\SocketClient $client Client that connected
	 */
	public function onClientConnected(
		PHP\WebSocket\SocketServer $server,
		PHP\WebSocket\SocketClient $client
	) {
        if (array_key_exists("connect", $this->app)) {
            $app = $this->app["connect"];
            $app($server, $client);
        }
    }

	/**
	 * Called when a  client disconnects from the server.
	 *
	 * @param PHP\WebSocket\SocketServer $server The server instance
	 * @param PHP\WebSocket\SocketClient $client Client that disconnected
	 */
	public function onClientDisconnected(
		PHP\WebSocket\SocketServer $server,
		PHP\WebSocket\SocketClient $client
	) {
        if (array_key_exists("disconnect", $this->app)) {
            $app = $this->app["disconnect"];
            $app($server, $client);
        }
    }

	/**
	 * Called when the server generates a log message.
	 *
	 * @param PHP\WebSocket\SocketServer $server The server
	 * @param string $message Log message
	 */
	public function onLogMessage(
		PHP\WebSocket\SocketServer $server,
		$message
	) {
        if (array_key_exists("log", $this->app)) {
            $app = $this->app["log"];
            $app($server, $message);
        }
    }

    /**
     * @var string[]
    */
    static $appNames = [
        "message"    => 0,
        "connect"    => 1,
        "disconnect" => 2,
        "log"        => 3
    ];

    /**
     * + message(PHP\WebSocket\SocketServer, PHP\WebSocket\SocketClient, $message)
     * + connect(PHP\WebSocket\SocketServer, PHP\WebSocket\SocketClient)
     * + disconnect(PHP\WebSocket\SocketServer, PHP\WebSocket\SocketClient)
     * + log(PHP\WebSocket\SocketServer, $message)
    */
    public function on($name, $handler) {
        if (array_key_exists($name = strtolower($name), self::$appNames)) {
            $this->app[$name] = $handler;
        } else {
            throw new Exception("[$name] is not recognized as any WS app!");
        }
    }
}

class Websocket {

    /**
     * @var PHP\WebSocket\SocketServer
    */
    private $socket;

	/**
	 * Constructor, sets host and port to bind to.
	 *
	 * @param string $host Socket host to bind to, defaults to localhost
	*/
	public function __construct(
		$host = '127.0.0.1'
	) {
		$this->socket = new PHP\WebSocket\SocketServer($host);
    }
    
    /**
     * 在这个函数之中添加应用程序逻辑
     * 
     * @return Websocket
    */
    public function Handles(PHP\WebSocket\SocketListener $app) {
        $this->socket->addListener($app);
        return $this;
    }

    public function listen($port, $maxConnections = SOMAXCONN) {
        $this->socket->listen($port, $maxConnections);
    }
}