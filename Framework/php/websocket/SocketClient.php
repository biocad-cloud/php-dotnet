<?php

namespace PHP\WebSocket;

Imports("System.IDisposable");

/**
 * Class representing a WebSocket client.
 */
class SocketClient implements \System\IDisposable {

	/**
	 * Number of instances created.
	 *
	 * @var integer
	*/
	static $instances = 0;

	#region "Properties"

	/**
	 * Reference to server that created the client.
	 *
	 * @var SocketServer
	*/
	public $server;

	/**
	 * Client id.
	 *
	 * This starts from one and is incremented for every connecting user.
	 *
	 * @var integer
	*/
	public $id;

	/**
	 * Client socket.
	 *
	 * @var resource
	*/
	public $socket;

	/**
	 * Client state.
	 *
	 * One of SocketClient::STATE_.. constants.
	 *
	 * @var integer
	*/
	public $state;

	/**
	 * The ip of the client.
	 *
	 * @var string
	 */
	public $ip;

	/**
	 * The port of the client.
	 *
	 * @var integer
	 */
	public $port;

	/**
	 * The time data was last recieved from the client.
	 *
	 * @var integer
	 */
	public $lastRecieveTime = 0;

	/**
	 * Last time data was sent to this client.
	 *
	 * @var integer
	 */
	public $lastSendTime = 0;

	/**
	 * Any data associated with the user.
	 *
	 * @var mixed
	 */
	public $data = [];

	#endregion

	/**
	 * User is connecting, handshake not yet performed.
	 */
	const STATE_CONNECTING = 0;

	/**
	 * Connection is valid.
	 */
	const STATE_OPEN       = 1;

	/**
	 * Connection has been closed.
	 */
	const STATE_CLOSED     = 2;

	/**
	 * Constructor, sets the server that spawned the client and the socket.
	 *
	 * @param SocketServer $server Parent server
	 * @param resource $socket User socket
	 * @param integer $state Initial state
	 */
	public function __construct(
		SocketServer $server,
		$socket,
		$state = self::STATE_CONNECTING
	) {
		self::$instances++;

		$this->server = $server;
		$this->id = self::$instances;
		$this->socket = $socket;
		$this->state = $state;
		$this->lastRecieveTime = time();

		socket_getpeername($socket, $this->ip, $this->port);
	}

	/**
	 * Sends a message to the client
	 *
	 * @param mixed $message Message to send
	 */
	public function send($message) {
		if ($this->state == self::STATE_CLOSED) {
			throw new \Exception(
				'Unable to send message, connection has been closed'
			);
		}

		$this->server->send($this->socket, $message);
	}

	/**
	 * Sets client property.
	 *
	 * @param string $name Property name
	 * @param mixed $value Property value
	 */
	public function set($name, $value) {
		$this->data[$name] = $value;
	}

	/**
	 * Returns client property.
	 *
	 * @param string $name Name of the property
	 * @param mixed $default Default value returned when property does not exist
	 * @return mixed
	 */
	public function get($name, $default = null) {
		if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		} else {
			return $default;
		}
	}

	/**
	 * Disconnects the client.
	 */
	public function disconnect() {
		if ($this->state == self::STATE_CLOSED) {
			return;
		}

		$this->server->disconnectClient($this->socket);
	}

	/**
	 * Does the magic handshake to begin the connection
	 *
	 * @param string $buffer Buffer sent by the client
	 * @return bool Was the handshake successful
	 * @throws \Exception If something goes wrong
	 */
	public function performHandshake($buffer) {
		if ($this->state != self::STATE_CONNECTING) {
			throw new \Exception(
				'Unable to perform handshake, client is not in connecting state'
			);
		}

		$headers = self::parseRequestHeader($buffer);
		$key     = $headers['Sec-WebSocket-Key'];
		$hash    = base64_encode(
			# 2018-09-21 下面的guid是一个magic string，永远不会变的常量
			sha1($key.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true)
		);

		$headers = [
			'HTTP/1.1 101 Switching Protocols',
			'Upgrade: websocket',
			'Connection: Upgrade',
			'Sec-WebSocket-Accept: '.$hash
		];
		$headers = implode("\r\n", $headers) . "\r\n\r\n";
		$left    = strlen($headers);

		do {
			$sent = @socket_send($this->socket, $headers, $left, 0);

			if ($sent === false) {
				$error = $this->server->getLastError();

				throw new \Exception(
					'Sending handshake failed: : '.$error->message.
					' ['.$error->code.']'
				);
			} else {
				$left -= $sent;
			}

			if ($sent > 0) {
				$headers = substr($headers, $sent);
			}
			
		} while($left > 0);

		$this->state = self::STATE_OPEN;
	}

	/**
	 * Parses the request header into resource, headers and security code
	 *
	 * @param string $request The request
	 * @return array Array containing the resource, headers and security code
	 */
	private static function parseRequestHeader($request) {
		$headers = [];

		foreach (explode("\r\n", $request) as $line) {
			if (strpos($line, ': ') !== false) {
				list($key, $value) = explode(': ', $line);

				$headers[trim($key)] = trim($value);
			}
		}

		return $headers;
	}

    public function __toString() {
        return $this->ip;
	}
	
	public function Dispose() {
		$this->disconnect();
	}
}