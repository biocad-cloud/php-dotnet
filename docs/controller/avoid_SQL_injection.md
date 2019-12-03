# Avoid SQL Injection

There are two method that can be used for avoid the unexpected query argument and the **SQL Injection** in php.NET framework:

## 1. ``require`` meta data

A ``require`` meta data tag is a good choice for avoid the SQL injection, example as:

```php
<?php

/**
 * @require id=i32
*/
public function queryById() {
    # SELECT * FROM `table` WHERE id = 'xxx';
    controller::success($model->where(["id" => $_GET["id"]])->select());
}
```

The example web app controller code that show above will accept the integer id parameter value only! If the parameter value of the id parameter is not an integer value then a ``400 bad request`` http error will be triggered.

if we removes the require meta tag data, then a url query contains sql injection data expression like ``id=~>0`` will produce the SQL query like:

```sql
SELECT * FROM `table` WHERE id > 0;
```

A sql injection attack event will happened! A details document about write mysql expression could see this document: [**&lt;MySql Expression Value>**](../../docs/model/expression.md), document about using the meta tag data in php.NET framework, see this document: [**&lt;Meta data in your WebApp>**](../../docs/controller/meta.md) 

## 2. ``request`` helper

Except the ``@require`` meta tag define on your web app, a ``request`` helper class is also can help your web app to avoid the SQL injection, example as:

```php
<?php

Imports("MVC.request");

public function queryById() {
    $query = $model
        ->where(["id" => WebRequest::getInteger("id")])
        ->select();
}
```

Then you can make sure that the id parameter is always an integer value, any other non numeric parameter value will be convert to integer value ``ZERO``.

And here is a list of avaiable get parameter helper methods in the ``WebRequest`` helper class:

```php
<?php

# Get true/false logical value from the query parameters
# If the key is not presented in query, then returns false
# otherwise parse boolean value from the parameter value string 
WebRequest::getBool($key);

# Get an integer value from the specific query parameter,
# Any non-numeric parameter value will be convert to ZERO
WebRequest::getInteger($queryKey, $default = 0, $unsigned = true);
WebRequest::getNumeric($queryKey, $default = 0.0, $unsigned = true);

# Get a file path components that comes from 
# the url query parameter or post arguments
WebRequest::getPath($queryKey, $default = NULL, $raw = FALSE);

# Get a item list from the query parameter value string,
# The items in the list is split by the specific delimiter symbol
# comma by default
WebRequest::getList($queryKey, $delimiter = ",");
```