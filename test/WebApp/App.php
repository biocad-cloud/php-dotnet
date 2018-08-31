<?php

class App {

    public function index() {
        $this->exceptionTest();
    }

    public function exceptionTest() {
        dotnet::ThrowException("Test for stack trace!");
		
		echo "This message will never show!";
    }
	
	/**
	 * @uses view
	*/
	public function volist() {		
		View::Display(["persons" => [
			["name" => "a", "age" => 55],
			["name" => "a1", "age" => 77],
			["name" => "a2", "age" => 33],
			["name" => "a3", "age" => 56],
			["name" => "a4", "age" => 45]
		]]);		
	}

	/**
	 * @uses view
	 * @access *
	*/
	public function aesTest() {
		Imports("Microsoft.VisualBasic.Net.OPENSSL_AES");

		$aes = new AES128CBC("1234567890abcdef");
		$message = $aes->Encrypt("hello world!");

		echo "\n" . $message . "\n\n";

		$message = $aes->Decrypt($message);

		echo "Raw message is:  [$message]";
	}

	public function aes() {
		View::Display();
	}

	public function aes_message() {
		Imports("Microsoft.VisualBasic.Net.OPENSSL_AES");

		$aes = new AES128CBC("1234567890abcdef");
		$message = $aes->Encrypt("hello world!");

		controller::success([
			"msg" => $message, 
			"key" => $aes->key
		]);
	}
}
?>