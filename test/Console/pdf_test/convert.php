<?php

include "../../../package.php";

Imports("php.WkHtmlToPdf");

$page = new \PHP\WkHtmlToPdf\Options\Page();
$page->allow = "test.js";
$page->nobackground = true;

echo var_dump($page);
echo var_dump(get_class($page));
echo var_dump($page->ToString());