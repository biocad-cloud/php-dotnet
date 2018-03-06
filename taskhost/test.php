<?php

include_once(dirname(__FILE__) . "/taskhost.php");

$signals = "/tmp/test_signals.txt";
$host    = new taskhost($signals, 1);
$i       = 0

$host->run(function() {
    echo ++$i . "\n";
});

?>