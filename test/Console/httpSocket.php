<?php

include __DIR__ . "../../../package.php";

dotnet::AutoLoad();

Imports("php.http");

$http = new httpSocket("127.0.0.1", null, 85);
$http->Run(); 