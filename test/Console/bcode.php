<?php

$args = [
    "resolution" => 10000
];

include dirname(dirname(__DIR__)) . "/package.php";

imports("php.BEncode.autoload");

print(Rych\Bencode::encode($args));