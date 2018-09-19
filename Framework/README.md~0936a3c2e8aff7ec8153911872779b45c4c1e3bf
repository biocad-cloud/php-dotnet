# App Framework Usage

### 1. Require php.NET framework

```php
include "../package.php";
```

### 2. Load php.NET framework kernel

```php
dotnet::AutoLoad("etc/config.php");
```

Where the ``config.php`` should return an array table, example:

```php
<?php

# config.php demo
#
# A very basic configuration file: just contains mysql connection 
# parameter configuration for php.NET
return [
	'DB_TYPE' => 'mysql',
	'DB_HOST' => 'localhost',
	'DB_NAME' => 'mz_biodeep_cn',
	'DB_USER' => 'root',
	'DB_PWD'  => 'root',
	'DB_PORT' => '3306'
];

?>
```

You can ignore specific the configuration parameter, like:

```php
dotnet::AutoLoad();
```

This statement works, but the MySql module in this framework will not working as no database connection parameter is setting up.

### 3. Create App for handle http request

Just create a normal php class definition and contains some public instance function members:

```php
# index.php

class App {

    public function test() {
        echo "Hello world!";
    }

    public function another_test() {
        echo "Another API working: " . $_GET["word"] ;
    }
}
```

that's it!

### 4. Apply http request router

```php
dotnet::HandleRequest(new App());
```

URL router rule in php.NET:

```
{fileName/function}&url_parameters
```

Example as:

+ ``{index/test}`` is roughly equivalent to url: ``/index.php?app=test``. So this url api calls will output:
   
```
Hello world!
```

+ ``{index/another_test}&word=12345`` is roughly equivalent to url: ``/index.php?app=another_test&word=12345``. So this url api calls will output:

```
Another API working: 12345
```

# Using MySql

* MySql module only works when the configuration is presented.

### 1. Create table model

```php
$users = new Table("users");
```

### 2. SELECT query

```php
# SELECT * FROM `users`;
$all_users = $users->All();

# SELECT * FROM `users` WHERE `is_online` = '1' AND `gender` = '1';
$online_list = $users
    ->where([
        "is_online" => 1, 
        "gender"    => 1
    ])
    ->select();

# SELECT * FROM `users` WHERE `is_online` = '1' AND `gender` = '1' LIMIT 1;
$online_list = $users
    ->where([
        "is_online" => 1, 
        "gender"    => 1
    ])
    ->find();

# SELECT * FROM `users` WHERE `is_online` = '1' AND `gender` = '1' LIMIT 5,10;
$online_list = $users
    ->where([
        "is_online" => 1, 
        "gender"    => 1
    ])->limit(5, 10)
      ->select();
```

### 3. INSERT INTO

```php
# INSERT INTO `users` (`name`, `gender`) VALUES ('Jack', '1');
$users->add([
    "name"   => "Jack", 
    "gender" => "1"
]);
```

### 4. UPDATE

```php
# UPDATE `users` SET `name` = 'ABC', `gender` = '0' WHERE `id` = '5' LIMIT 1;
$users->where(["id" => 5])
      ->limit(1)
      ->save([
          "name"   => "ABC", 
          "gender" => "0"
      ]);
```


### 5. DELETE FROM

```php
# DELETE FROM `users` WHERE `id`='5' LIMIT 1;
$users->where(["id" => 5])
      ->limit(1)
      ->delete();
```

## Advanced MySql Model Usage

### LIKE

```php
# DELETE FROM `users` WHERE (`name` LIKE '%abc') OR (`title` LIKE '%abc') LIMIT 10;
$users->where(["name|title" => like("%abc")])
      ->limit(10)
      ->delete();
```

### IN

```php
# DELETE FROM `users` 
# WHERE ((`name` LIKE '%abc') OR (`title` LIKE '%abc')) AND (mod(`score` + 5) IN ('1','2','3','4','5','6','7','8','9'))
# LIMIT 10;
$users->where(["name|title" => like("%abc")])
      ->and([
      	   "mod(`score` + 5)" => in(1,2,3,4,5,6,7,8,9)
      ])
      ->limit(10)
      ->delete();
```

### BETWEEN

```php
# UPDATE `users` 
# SET `flag` = '99' 
# WHERE `id` BETWEEN '100' AND '5000' 
# LIMIT 1000;
$users->where(["id" => between(100, 5000)])      
      ->limit(1000)
      ->save(["flag" => 99]);
```

## Table Join

```php
$query = (new Table("chk_order_member"))
    ->left_join("chk_box_order")
    ->on(["chk_box_order"    => "order_id", 
	  "chk_order_member" => "order_id"
      ])
    ->left_join("chk_box")
    ->on(["chk_box"       => "box_id", 
	  "chk_box_order" => "box_id"
      ])
    ->where($predicate)
    ->order_by(["`chk_box`.`box_id`", "`chk_box_order`.`index`", "`chk_order_member`.`order_sn`"])
    ->select([
	"chk_order_member.order_sn as barcode", 
	"chk_order_member.id as uid",
	"chk_order_member.*", 
	"chk_box.*", 
	"chk_box_order.*"
    ]);
```

Will generates the SQL expression like:

```sql
SELECT 
  chk_order_member.order_sn as barcode, 
  chk_order_member.id as uid, 
  chk_order_member.*, 
  chk_box.*, 
  chk_box_order.* 
FROM 
  `t17`.`chk_order_member` 
  LEFT JOIN `chk_box_order` ON (
    `chk_box_order`.`order_id` = `chk_order_member`.`order_id`
  ) 
  LEFT JOIN `chk_box` ON (
    `chk_box`.`box_id` = `chk_box_order`.`box_id`
  ) 
WHERE 
  (
    (
      is_smoke IN ('0', '1', '2')
    )
  ) 
  AND (
    (
      is_drink IN ('0', '1', '2')
    )
  ) 
  AND (
    (uname <> '')
  ) 
  AND (
    (
      `chk_order_member`.`order_sn` <> ''
    )
  ) 
ORDER BY 
  `chk_box`.`box_id`, 
  `chk_box_order`.`index`, 
  `chk_order_member`.`order_sn`;
```
