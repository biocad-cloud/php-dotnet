<?php

include "../../package.php";

dotnet::AutoLoad();

Imports("System.Text.RegularExpressions.Regex");

$str = "@A@B@C@D@E";
$pattern = "@.+?";

$matches = Regex::Matches($str, $pattern);

echo json_encode($matches);

?>