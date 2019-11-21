<?php


include __DIR__ . "/../../package.php";

dotnet::AutoLoad();

Imports("Debugger.SqlFormatter");

$sql = "select * from `a`.`ghgghg` where 1=1 or (delete * from `n` limit 1) or (drop database `xxx`);";
$tokens = SqlFormatter::tokenize($sql);


echo var_dump($tokens);