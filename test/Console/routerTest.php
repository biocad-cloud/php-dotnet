<?php

include __DIR__ . "/../../package.php";

Imports("php.htaccess");
Imports("MVC.router");

$url = "dict.php?app=search&q=browse&FORM=BDVSP6&mkt=zh-cn";

console::dump(Router::mb_parse_url($url));