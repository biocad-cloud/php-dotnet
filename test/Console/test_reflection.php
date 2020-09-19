<?php

include __DIR__ . "/../../package.php";

class App {
	
	public function a($a, $b, $c = 55, $d = FALSE) {
		echo var_dump($a);
		echo var_dump($b);
		echo var_dump($c);
		echo var_dump($d);
	}
	
}

$appObj = new App();
$app = "a";

imports("MVC.handler.appCaller");

\MVC\Controller\appCaller::doCall($appObj, $app);