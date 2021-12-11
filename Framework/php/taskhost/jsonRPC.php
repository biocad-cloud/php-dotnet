<?php

class jsonRPC {

    private $app;

    function __construct($app) {
        $this->app = $app;
    }

    public function call($rpc) {

    }

    public static function handleRPC($app, $rpc) {
        return (new jsonRPC($app))->call($rpc);
    }
}