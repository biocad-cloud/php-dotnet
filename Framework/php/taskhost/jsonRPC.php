<?php

imports("MVC.handler.appCaller");

class jsonRPC {

    private $app;

    function __construct($app) {
        $this->app = $app;
    }

    /**
     * @param rpc [jsonrpc, method, params, id]
    */
    public function call($rpc) {
        $id = $rpc["id"];
        $method = $rpc["method"];
        $params = $rpc["params"];

        if (!method_exists($this->app, $method)) {
            $this->methodNotFound($rpc);
            die;
        }

        return MVC\Controller\appCaller::CallWithPayload($this->app, $method, $params, TRUE);
    }

    private function methodNotFound($rpc) {
        $method = $rpc["method"];
        $id = $rpc["id"];

        echo json_encode([
            "jsonrpc" => "2.0",
            "error" => ["code" => -32601, "message" => "method '$method' is not exists!"],
            "id" => $id
        ]);
    }

    public static function handleRPC($app, $rpc) {
        return (new jsonRPC($app))->call($rpc);
    }
}