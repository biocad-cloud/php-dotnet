<?php

include __DIR__ . "/../../package.php";

Imports("php.taskhost.taskhost");

for($i = 0; $i < 1300; $i++) {
    echo taskhost::getNextTempName() . "\t";
}