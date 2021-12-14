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
        $params = $rpc["params"];echo var_dump($params);die;
        $params["rpc"] = $rpc;
        $payload = new MVC\Controller\JsonPayload($params);

        if (!method_exists($this->app, $method)) {
            $this->methodNotFound($rpc);
            die;
        }

        return MVC\Controller\appCaller::CallWithPayload($this->app, $method, $payload, TRUE);
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

    public static function success($data, $id) {
        header("HTTP/1.1 200 OK");
        header("Content-Type: application/json");

        echo json_encode([
            "jsonrpc" => "2.0",
            "result" => $data,
            "id" => $id
        ]);

        if (APP_DEBUG) {
            dotnet::$debugger->WriteDebugSession();
        }

        exit(0);
    }

    public static function error($message, $code = -32000) {
        header("HTTP/1.1 200 OK");
        header("Content-Type: application/json");

        echo json_encode([
            "jsonrpc" => "2.0",
            "error" => ["code" => $code, "message" => $message]          
        ]);

        if (APP_DEBUG) {
            dotnet::$debugger->WriteDebugSession();
        }

        exit(0);
    }
}