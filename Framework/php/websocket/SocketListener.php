<?php


/**
 * Socket server listener interface.
 *
 * Implement this to communicate with the socket server.
 *
 * @author Priit Kallas <kallaspriit@gmail.com>
 * @package WebSocket
 */
interface SocketListener {

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
	);

	/**
	 * Called when a new client connects to the server.
	 *
	 * @param SocketServer $server The server instance
	 * @param SocketClient $client Client that connected
	 */
	public function onClientConnected(
		SocketServer $server,
		SocketClient $client
	);

	/**
	 * Called when a  client disconnects from the server.
	 *
	 * @param SocketServer $server The server instance
	 * @param SocketClient $client Client that disconnected
	 */
	public function onClientDisconnected(
		SocketServer $server,
		SocketClient $client
	);

	/**
	 * Called when the server generates a log message.
	 *
	 * @param SocketServer $server The server
	 * @param string $message Log message
	 */
	public function onLogMessage(
		SocketServer $server,
		$message
	);
}