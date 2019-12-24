<?php

include __DIR__ . "../../../package.php";

dotnet::AutoLoad();

imports("php.websocket");

$app = (new WsAppHandler())
	/**
	 * Called when a client sends a message to the server.
	 *
	 * @param PHP\WebSocket\SocketServer $server The server instance
	 * @param PHP\WebSocket\SocketClient $client Client that sent the message
	 * @param string $message Sent message
	*/
	  ->on("message", function($server, $sender, $message) {
		$sender->send(" say hello from: " . date("Y-m-d H:i:s"));
		echo "[~] $message\n";
	/**
	 * Called when a new client connects to the server.
	 *
	 * @param PHP\WebSocket\SocketServer $server The server instance
	 * @param PHP\WebSocket\SocketClient $client Client that connected
	*/
	})->on("connect", function($server, $client) {
		echo "[+] $client\n";
	/**
	 * Called when a  client disconnects from the server.
	 *
	 * @param PHP\WebSocket\SocketServer $server The server instance
	 * @param PHP\WebSocket\SocketClient $client Client that disconnected
	*/
	})->on("disconnect", function($server, $client) {
		echo "[-] $client\n";
	/**
	 * Called when the server generates a log message.
	 *
	 * @param PHP\WebSocket\SocketServer $server The server
	 * @param string $message Log message
	*/
	})->on("log", function($server, $message) {
		echo "[:] $message\n";
	});

(new Websocket("localhost"))->Handles($app)->listen(5005);