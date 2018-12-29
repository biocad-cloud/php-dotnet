# php.NET
dotnet class simulation in php language

> [WARNING] This project is a work in progress and is not recommended for production use.

It contains:

+ A MVC framework for Web App
+ A CLI framework for terminal scripting.

## How to use?

1. Clone this repository into one of the directory in your php project folder
2. And then add includes to the package manager module:
   ```php
   <?php
       include_once "./mod/php-dotnet/package.php";
   ?>
   ```
3. If you want using one of the module in this project, just includes it, example:
   ```php
   <?php
       include_once "./mod/php-dotnet/package.php";

       Imports("System.Collection.Generic.Dictionary");
       Imports("Microsoft.VisualBasic.Language.List");
       Imports("Microsoft.VisualBasic.Conversion");

       $double = Conversion::Val("123");
       $list   = new ArrayList;
       $list->Add($double);
   ?>
   ```
4. Enjoy yourself coding with this package

## The improved Debugger

.NET system like stack trace information, example as:

```php
<?php
   Imports('System.Diagnostics.StackTrace');
?>

# at StackTrace::GetCallStack in /modules/dotnet/dotnet.php:line 268
# at dotnet::PageNotFound in /modules/dotnet/MVC/controller.php:line 202
# at controller::Hook in /modules/dotnet/dotnet.php:line 84
# at dotnet::HandleRequest in /bootstrap.php:line 23
# at include in /index.php:line 3
# --- End of inner exception stack trace ---
```
