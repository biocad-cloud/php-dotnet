# php.NET Framework

dotnet class and framework simulation in php language

> [WARNING] This project is a work in progress and is not recommended for production use.

```
............................................................
............................................................
..............TT.................TNT....TT..TNNNNTTNNNNNNT..
..............E..................hNh....h...h........E......
..............E..................hhE....h...h........E......
.....TTTNNT..TTTNNT..TTTNNT.....TT.NT..TT..TT.......TT......
.....EN...E..EN..Th..EN...E.....h..Eh..h...hNNNE....h.......
.....E....E..E...TT..E....E.....h..TE..h...h........h.......
....TT....h.TT...E..TT....h....TT...N.TT..TT.......TT.......
....E....h..h....h..E....h.....h....Ehh...h........h........
....NT..hT..h...TT..NT..hT.Th..h....TNh...h........h........
...TThNNT..TT...h..TThNNT..ET.TT.....NT..TNNNNT...TT........
...h...............h........................................
...h...............h........................................
..TT..............TT........................................
............................................................
```

It contains:

+ A MVC framework for Web App
+ A CLI framework for commandline scripting.

## How to use?

1. Clone this repository into one of the directory in your php project folder
2. And then add includes to the package manager module:
   ```php
   <?php
       include_once "./php.NET/package.php";
   ```
3. If you want using one of the module in this project, just includes it, example:
   ```php
   <?php
       include_once "./php.NET/package.php";

       Imports("System.Collection.Generic.Dictionary");
       Imports("Microsoft.VisualBasic.Language.List");
       Imports("Microsoft.VisualBasic.Conversion");

       $double = Conversion::Val("123");
       $list   = new ArrayList;
       $list->Add($double);
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
