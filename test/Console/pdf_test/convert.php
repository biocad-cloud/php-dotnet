<?php

include "../../../package.php";

imports("php.WkHtmlToPdf");

$page = new \PHP\WkHtmlToPdf\Options\Page();
$page->allow = "test.js";
$page->nobackground = true;

# echo var_dump($page);
# echo var_dump(get_class($page));
echo var_dump($page->ToString());

# test enum
$size = \PHP\WkHtmlToPdf\Options\QPrinter::A6;
$str  = \PHP\WkHtmlToPdf\Options\QPrinter::ToString($size);

echo var_dump($str);

$val = "B9";  //= 23
$size = \PHP\WkHtmlToPdf\Options\QPrinter::TryParse($val);

echo var_dump($size);