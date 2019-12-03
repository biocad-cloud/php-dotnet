# Message Protocol

The php.NET message protocol is only works for api controller, which the api controller is response a json text data to the client.

The responsed json text is in format like:

```json
{
    "code": error_code,
    "info": ...,
    ["debug": ...]
}
```

And you can produce such json response by invoke the ``success`` and ``error`` method in the ``controller`` class module.

For a web app request process success, then the ``error_code`` for the ``code`` property value in json response will be ZERO. **Any other non-ZERO** error code in the response json means error while in the process of the web app request.

The ``info`` property value is the response data of your web app. If the web app request process is success, then this property value should be the result value that returns to your client. And if the process failure, then this ``info`` property value should be the error message for display on the client. The ``debug`` property is optional, and it only appears in the json response when the web app request process is in error status.

## Invoke response method

For a success web app request processing result, you should returns the result to the client by invoke method ``controller::success``:

```php
<?php

/**
 * 在完成了这个函数的调用之后，服务器将会返回成功代码
 * 并退出当前的脚本执行状态
 * 
 * @param string $message 需要通过json进行传递的消息文本
 * @return void
*/
public static function success($message) {
    header("HTTP/1.1 200 OK");
    header("Content-Type: application/json");

    echo dotnet::successMsg($message);

    if (APP_DEBUG) {
        dotnet::$debugger->WriteDebugSession();
    }

    exit(0);
}

# example as
controller::success(["user_name" => "abc", "id" => [1,2,3]]);
```

Will produce the json response looks like:

```json
{
    "code": 0,
    "info": {
        "user_name": "abc",
        "id": [1, 2, 3]
    }
}
```

For a failure web app request processing result, you should returns the result to the client by invoke method ``controller::error``:

```php
<?php

/**
 * 在完成了这个函数的调用之后，服务器将会返回错误代码
 * 并退出当前的脚本执行状态
 * 
 * @param string $message 需要通过json进行传递的消息文本
 * @param integer $errCode 错误代码，默认为1
 * 
 * @return void
*/
public static function error($message, $errCode = 1, $debug = null) {
    header("HTTP/1.1 200 OK");
    # 2018-10-11 不能够在这里设置500错误码，这个会导致
    # jquery的success参数回调判断失败，无法接受错误消息
    # header("HTTP/1.0 500 Internal Server Error");
    header("Content-Type: application/json");

    echo dotnet::errorMsg($message, $errCode, $debug);

    if (APP_DEBUG) {
        dotnet::$debugger->WriteDebugSession();
    }

    exit($errCode);
}

# example as
controller::error("User not found!", 404);
```

Will produce the json response looks like:

```json
{
    "code": 404,
    "info": "User not found!"
}
```