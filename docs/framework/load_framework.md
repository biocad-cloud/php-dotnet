# Load Framework

For using the php.NET framework for running you php web app, then you should includes the packages bootstrap loader script file at first.

```php
<?php

# load WebFramework
include __DIR__ . "/../php.NET/package.php";
```

And then you are about to load php.NET registry configuration file and running the web app through ``HandleRequest`` method:

```php
<?php

// 框架加载配置文件，并执行所请求的控制器函数

dotnet::AutoLoad(getConfig());
dotnet::HandleRequest(new App(), new accessController());

// 脚本执行结束
```

### Load modules

There are some necessary module that are loaded into memory when the web app handling the user web request by default. And you can load other php.NET module simply by calling a global method ``Imports``, example as:

```php
<?php

# The module load method is similar to the namespace imports
# action in VisualBasic.NET language.
Imports("Microsoft.VisualBasic.FileIO.FileSystem");

# The example method invoke that show above can be translate as
# the naive php code as:
include_once PHPDOTNET . "/Framework/Microsoft/VisualBasic/FileIO/FileSystem.php";
```

