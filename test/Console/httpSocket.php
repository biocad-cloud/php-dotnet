<?php

include __DIR__ . "../../../package.php";

dotnet::AutoLoad();

imports("php.http");

class services {
	
	public function hello($request) {
		$echo = $request["echo"];

		return "message: hello {$echo}!";
	}
}


$http = new httpSocket("127.0.0.1", 85);
$http->Run(new services()); 