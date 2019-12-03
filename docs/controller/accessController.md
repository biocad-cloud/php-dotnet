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
            "user_id"    => BioDeep::LoginUserId(),
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

## Apply the accessController

Just create a new instance of your ``accessController`` class object, and then passing it to the second parameter of the ``dotnet::HandleRequest`` function for you web app ``App`` class object instance:

```php
<?php

include "./modules/dotnet/package.php";
include "./accessController.php";

dotnet::AutoLoad("./etc/config.php");
dotnet::HandleRequest(new App(), new accessController());
```
