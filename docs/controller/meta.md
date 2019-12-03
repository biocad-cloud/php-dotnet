# Meta data in your WebApp

Generally, you can add some tags data on your web app controller function, and the ``accessController`` class can utilize these tags data in the comment docs. A list of meta data tags that supported in php.NET framework in php comment docs is shown below:

+ ``access`` tag for specific that the access control user group, like ``admin``, ``user`` group, etc, and anything else that you like. A special character ``*`` means this web app can be accessed by everyone. If the request user is not in one of the user role group, then a ``403 Forbidden`` error will be triggered.
+ ``uses`` tag will tells the web server how to send the content type http header to the user browser, this tag data has 4 kinds of value:

   + ``view`` for html view page, Content-Type: ``text/html``
   + ``api`` for json data response, Content-Type: ``application/json``
   + ``soap`` for xml data response, Content-Type: ``text/xml``
   + ``router`` means current web app will redirect the user browser to a new location.
   + ``text`` for plain text, Content-Type: ``text/plain``

   This meta tag will produce the content type http header like: ``Content-Type: application/json``.

+ ``accept`` config the ip white list that access of current web app resource. If the incomming http request its client ip is not in the whitelist that specific by this meta tag value, then a ``403 Forbidden`` error will be returned to the client. A special word ``localhost`` for this meta tag value means the current web app resource is access by the request from local machine only! The ip list on the white list could be combine with a ``|`` symbol as delimiter.
+ ``origin`` config the so called ``same-origin policy`` for the current web app controller of your server resource, the value for this meta value tag is a list of origin domain name. A special symbol ``*`` means no limitation on the ``Cross-Origin`` resource sharing. This meta tag will produce the http header like: ``Access-Control-Allow-Origin: <origin>``

+ ``require`` tag tells the php web server that the parameter names which listed in this require tag data must appears in the query parameters and the data type of the parameter value must match the pattern specific by the tag value is also required. The required parameters in this meta tag data is combine with ``|`` symbol as delimiter. The string pattern of the parameter value in this meta tag value is show below:

   + ``i32`` means the query parameter its value should be an integer value
   + ``boolean`` means the query parameter its value should be a logical value in text like 1/0/true/false/T/F, etc
   + ``string`` means the query parameter its value should not be empty!

   > If the parameter value validation is not success based on the pattern that define on your web app, 
   > then a ``400 bad request`` http error will be triggered.

+ ``cache`` tag can let you to controlling of the cache behaviour in the user browser. All of the controller response from the php is not cachable by default, but you could try this meta tag value to send a cache header to the user browser to force the browser cache the current resource, if the current resource is static file or something. All of the avaiable cache controls in this meta tag value are:

   + ``none`` tells the user browser do not cache current server resource.
   + ``max-age=<seconds>`` tells the user browser to cache of the current server resource.
Cache-control: must-revalidate
Cache-control: no-cache
Cache-control: no-store
Cache-control: no-transform
Cache-control: public
Cache-control: private
Cache-control: proxy-revalidate
Cache-Control: max-age=<seconds>
Cache-control: s-maxage=<seconds>

+ ``method`` tag tells the php web server how to accept the current http request. If the tag value of this ``method`` data tag is ``GET``, then it means only the GET request will be acceptted, POST method will trigger a ``405 method not allowed`` http error response. This meta data only supports GET or POST method.
+ ``debugger`` meta tag will turn on/off the php debugger for current web app. Note that the php debug only works for the html view controller, which means the ``@uses view`` configuration should be appears on your current web app controller. 
+ ``rate`` tag for restrict the user access of a specifci server resource, you can restrict the user request number in a time span, the time value has 3 kinds of unit: min, hour, day. And you can specific the request number for these time spans units, like:
   + ``60/min`` means user request number was limits to 60 http request every minute, once the request number is exceed 60 in one minute, then your server will returns a 429 error code.
   + ``1500/hour`` means user request number was limits to 1500 http request every hour, once the request number is exceed 1500 in one hour, then your server will returns a 429 error code.
   + ``3000/day`` means user request number was limits to 3000 http request every day(or 24 hours). once the request number is exceed 3000 in 24 hours, then your server will returns a 429 error code. 

### Examples

Examples of the web app meta data usage are show bellow:

1. The web app ``search`` can be accessed by every one(without login required), and with the request rate of 60 request per minutes or 1500 request per hour or 3000 request per day.

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

2. The web app ``querySQL`` require two parameters in the http request: ``sql`` and ``limit``. And the sql parameter should not be empty and the limit count parameter should be an integer value. And also this web app only allows the http request comes from the local machine. **Any http request comes from other machine will be rejected(``403 Forbidden``)!**

```php
<?php

/**
 * @accept localhost
 * @require sql=string|limit=i32
*/
public function querySQL() {
    # ...
}
```

3. The web app ``submit`` only allowes ``POST`` method. ``GET`` method will triggered a ``405 method not allowed`` http error response.

```php
<?php

/**
 * @method POST
*/
public function submit() {
    # ...
}
```

4. The web app ``file`` only allowes the ``GET`` method, and it also suggested that the user browser should cache of the requested server resource.

```php
<?php

/**
 * A static file proxy for server resource
 * 
 * @method GET
 * @access *
 * @cache max-age=360
 * @require resource=string
*/
public function file() {
    $resource = $_GET["resource"];
    $isGetVersion = WebRequest::getBool("ver");
    $file = INTERNAL . "/" . stripParentDots($resource);

    if ($isGetVersion) {
        controller::success(filetime($file));
    } else {
        Utils::PushDownload($file);
    }
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
