<?php

include "../package.php";

Imports("System.Threading.Thread");
Imports("php.Utils");

use System\Threading\Thread as Thread;

function sleepTest() {

    echo Utils::Now();

    # 0.5s
    System\Threading\Thread.Sleep(500);

    echo Utils::Now();

    # 3.5s
    Thread.Sleep(3500);

    echo Utils::Now();

}

sleepTest();

?>