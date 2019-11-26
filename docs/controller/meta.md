# Meta data in your WebApp

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

## Restrictions

As you can see, there is a ``rate`` tag data that you can write into the comment docs as meta data, so that you can apply the ``Restrictions`` controller on a specific server resource in your web app, you should implements this ``Restrictions`` feature in your access controller by overrides the function ``Restrictions``:

```php
<?php

/**
 * 对当前的用户在某一个服务器资源上的访问量控制
*/
public function Restrictions() {
    if (!$this->HasRateLimits()) {
        return false;
    } else {
        global $limitTest;
        $limitTest = (new Restrictions(BioDeep::LoginUserId(), $this));
        return $limitTest->Check();
    }
}
```

+ If this function returns ``false``, then means the specific server resource access restrictions is not exceed, your user can access the server resource normally;
+ If this function returns ``true``, which means your server resource access restrictions has been breached, then the current user access will be restricted, server will returns 429 error code until the rate limits is restored.

## Apply the accessController

Just create a new instance of your ``accessController`` class object, and then passing it to the second parameter of the ``dotnet::HandleRequest`` function for you web app ``App`` class object instance:

```php
<?php

include "./modules/dotnet/package.php";
include "./accessController.php";

dotnet::AutoLoad("./etc/config.php");
dotnet::HandleRequest(new App(), new accessController());
```