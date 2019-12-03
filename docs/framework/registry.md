# Configuration and Registry

The registry file is the configuration tools for your web app in php.NET framework. class ``DotNetRegistry`` is loaded for your web app by default.

### How to config php.NET registry?

For an instance example, see document ***&lt;Using MySql Model>**.

```php
<?php

return [
	'DB_TYPE' => 'mysql',
	'DB_HOST' => 'localhost',
	'DB_NAME' => 'mz_biodeep_cn',
	'DB_USER' => 'root',
	'DB_PWD'  => 'root',
	'DB_PORT' => '3306'
];
```

The example code snapshot shows that, a php.NET configuration registry file should be a simple value returns script. The returns value of the configuration data should be a ``[key => value]`` paris array data. Each key in the configuration array is a registry key in the configuration and the corresponding value is the configuration value which can be read by using the method example as:

```php
DotNetRegistry::Read($key, $default = NULL); 
```

### How to load registry configuration?

Just passing the config file its file path to the ``dotnet::AutoLoad`` method, example as:

```php
<?php

# Load registry configuration file
# the configuration contains mysql database connection, view file location, etc
# everything that needs for running a php.NET web app
dotnet::AutoLoad(__DIR__ . "/../../../biodeep/.etc/www.biodeep.cn.config.php");
```