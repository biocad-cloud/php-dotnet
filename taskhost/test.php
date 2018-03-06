<?php

include_once(dirname(__FILE__) . "/taskhost.php");

$signals = "/tmp/test_signals.txt";
$host    = new taskhost($signals, 1);
$i       = 0;

class test {

    public function test233() {
        return "12345" . "\n";
    }
}

# $host->run((new test())->test);

$host->run(function() use (&$i) {

    echo $i++ . "\n";   

    echo (new test())->test233();
});

?>