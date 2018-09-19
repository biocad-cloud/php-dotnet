<?php

include "../../package.php";

dotnet::AutoLoad();

Imports("php.websocket");

(new WebSocket("localhost", "258EAFA5-E914-47DA-95CA-C5AB0DC85B11"))
     ->createServer(function($data, $write) {
         $write(Utils::Now() . ": " . $data);
     })
     ->listen(5005);