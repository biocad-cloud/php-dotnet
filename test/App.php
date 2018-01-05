<?php

class App {

    public function index() {
        $this->exceptionTest();
    }

    public function exceptionTest() {
        dotnet::ThrowException("Test for stack trace!");
    }
}
?>