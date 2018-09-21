<?php

include __DIR__ . "../../../package.php";

dotnet::AutoLoad();

Imports("php.websocket.*");

class testApp implements SocketListener {

    /**
	 * Called when a client sends a message to the server.
	 *
	 * @param SocketServer $server The server instance
	 * @param SocketClient $client Client that sent the message
	 * @param string $message Sent message
	 */
	public function onMessageRecieved(
		SocketServer $server,
		SocketClient $sender,
		$message
	) {
        $sender->send(" say hello from: " . date("Y-m-d H:i:s"));

        echo "[~] $message\n";
    }

	/**
	 * Called when a new client connects to the server.
	 *
	 * @param SocketServer $server The server instance
	 * @param SocketClient $client Client that connected
	 */
	public function onClientConnected(
		SocketServer $server,
		SocketClient $client
	) {
        echo "[+] $client\n";
    }

	/**
	 * Called when a  client disconnects from the server.
	 *
	 * @param SocketServer $server The server instance
	 * @param SocketClient $client Client that disconnected
	 */
	public function onClientDisconnected(
		SocketServer $server,
		SocketClient $client
	) {
        echo "[-] $client\n";
    }

	/**
	 * Called when the server generates a log message.
	 *
	 * @param SocketServer $server The server
	 * @param string $message Log message
	 */
	public function onLogMessage(
		SocketServer $server,
		$message
	) {
        echo "[:] $message\n";
    }
}

$server =  new SocketServer("localhost", "5005");
$server->addListener(new testApp());
$server->start();