<?php

$asserts = [
    "lower(`account`)|lower(`email`)" => "1233333", 
    "lower(`password`)"               => "23333",
    "uid"                             => between(20, 600),
    "name&title"                      => not_like("%TTG.cc%")
];

$expression = \MVC\MySql\Expression\WhereAssert::AsExpression($asserts);

echo $expression . "\n\n";

?>