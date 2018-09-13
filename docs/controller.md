# Access Controller

For using the php.NET access controller, you should declare a controller class object and extends the php.NET internal controller class, example like:

```php
<?php

Imports("MVC.controller");
Imports("MVC.restrictions");

class accessController extends controller {
    # ...
}
```

If you look into the source code of the php.NET controller class, then you can find out that the controller class is an abstract class and has an abstract function:

```php
<?php

/**
 * 函数返回一个逻辑值，表明当前的访问是否具有权限，如果这个函数返回False，那么
 * web服务器将会默认响应403，访问被拒绝
 * 
 * @return boolean 当前的访问权限是否验证成功？
*/
abstract public function accessControl();
```

Which means you must overrides this accessControl function in you declared ``accessController`` class. This ``accessControl`` function is a function that you must write the code for determine that current request is allowed to access your server resource or not:

+ If you think current user request is allowed to access your server resource, then you should make this function return a logical value ``true``.
+ Otherwise, you should make this function return a logical value ``false``, which means your server will return a ``403 access denied`` error code to your user by default.

By default, when a user request is not allowed to access the server resource, then the ``redirect`` function will be calls, this redirect can redirect user to a login page or display a http error page, and you can overrides this ``redirect`` function in your ``accessController`` class, example like:

```php
<?php

/**
 * 假若没有权限的话，会执行这个函数进行重定向
*/
public function Redirect($code) {
    // 并且记录下用户的活动信息
    (new Table(["my_biodeep" => "activity"]))
        ->add([
            "user_id"    => Common::CurrentUserId(),
            "action"     => Utils::URL(),
            "time"       => Utils::Now(),
            "error_code" => $code
        ]);

    if ($code == 403) {
        Redirect("http://passport.biodeep.cn/");
    } else if ($code == 429) {
        global $limitTest;
        dotnet::TooManyRequests($limitTest->Description());
    } else {
        dotnet::ThrowException("Oops, unknown server error...");
    }
} 
```

## Meta data in your WebApp

Generally, you can add some tags data onto your web app, and the ``accessController`` class can utilize these tags data in the comment docs. The accessController can utilize 3 kinds of tags in the comment docs: 

+ ``access`` tag for specific that the access control user group, like ``admin``, ``user`` group, etc, and anything else that you like. A special character ``*`` means this web app can be accessed by everyone.
+ ``uses`` tag will tells the web server how to send the http header to the user browser, this tag data has 4 kinds of value: 
   + ``view`` for html view page
   + ``api`` for json data response
   + ``soap`` for xml data response
   + ``router`` means current web app will redirect the user browser to a new location.
+ ``rate`` tag for restrict the user access of a specifci server resource, you can restrict the user request number in a time span, the time value has 3 kinds of unit: min, hour, day. And you can specific the request number for these time spans units, like:
   + ``60/min`` means user request number was limits to 60 http request every minute, once the request number is exceed 60 in one minute, then your server will returns a 429 error code.
   + ``1500/hour`` means user request number was limits to 1500 http request every hour, once the request number is exceed 1500 in one hour, then your server will returns a 429 error code.
   + ``3000/day`` means user request number was limits to 3000 http request every day(or 24 hours). once the request number is exceed 3000 in 24 hours, then your server will returns a 429 error code. 

An example of the web app meta data is:

```php
<?php

/**
 * 数据库搜索
 * 
 * 按照化合物名称，数据库编号等进行BioDeep标准品库的检索
 * 
 * @access *
 * @uses view
 * @rate 60/min,1500/hour,3000/day
*/
public function search() {
    # ...
}
```