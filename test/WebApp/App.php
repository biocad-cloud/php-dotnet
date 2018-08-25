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
}
?>