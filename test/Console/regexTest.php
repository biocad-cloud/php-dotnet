<?php

include "../../package.php";

dotnet::AutoLoad();

Imports("System.Text.RegularExpressions.Regex");

$str = "@A@B@C@D@E";
$pattern = "@.+?";

$matches = Regex::Matches($str, $pattern);

echo json_encode($matches) . "\n\n\n";

$pattern2 = "@.+?";

$mmm = Regex::Match($str, $pattern2);

echo $mmm . "\n\n\n";


preg_match_all("#$pattern2#", $str, $matches, PREG_PATTERN_ORDER);

echo json_encode($matches) . "\n\n\n";


?>