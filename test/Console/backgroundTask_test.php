<?php

include_once(dirname(__FILE__) . "../../php/taskhost.php");

echo __FILE__ . "\n";
echo taskhost::ref . "\n";

$signals = "/tmp/test_signals.txt";
$i       = 0;

date_default_timezone_set('UTC');

class test {

    public function test233() {
        return "12345" . "\n";
    }
}

# $host->run((new test())->test);

(new taskhost($signals, 1))->run(

    function() use (&$i) {
        

        echo $i++ . "\n";
        echo date("d-m-Y h:i:s") . "\n";    

        echo (new test())->test233();


    }, array(1, 8, 9));

?>