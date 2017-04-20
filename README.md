> [WARNING] This project is a work in progress and is not recommended for production use.

# php-dotnet
dotnet class simulation in php language

## How to use?

1. Clone this repository into one of the directory in your php project folder
2. And then add includes to the package manager module:
   ```php
   <?php
       include_once("./mod/php-dotnet/package.php");
   ?>
   ```
3. If you want using one of the module in this project, just includes it, example:
   ```php
   <?php
       include_once("./mod/php-dotnet/package.php");

       dotnet::Imports("System.Collection.Generic.Dictionary");
       dotnet::Imports("Microsoft.VisualBasic.Language.List");
       dotnet::Imports("Microsoft.VisualBasic.Conversion");

       $double = Conversion::Val("123");
       $list   = new List;
       $list->Add($double);
   ?>
   ```
4. Enjoy yourself coding with this package

## The improved Debugger

.NET system like stack trace information, example as:

```php
<?php
   dotnet::Imports('System.Diagnostics.StackTrace');
?>

at GetCallStack in D:\Develop\mod\php.NET\test\index.php:line 7
at stackTraceTest in D:\Develop\mod\php.NET\test\index.php:line 11
at __caller2 in D:\Develop\mod\php.NET\test\index.php:line 15
at __caller3 in D:\Develop\mod\php.NET\test\index.php:line 19
at __initCaller in D:\Develop\mod\php.NET\test\index.php:line 22
--- End of inner exception stack trace ---
```
