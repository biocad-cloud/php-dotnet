<?php

include "../../package.php";

dotnet::AutoLoad();

imports("Microsoft.VisualBasic.Strings");
imports("Microsoft.VisualBasic.Extensions.StringHelpers");

$str = "<foreach @variableName>& blablablabla     <ul>afsdasdad</ul>    &</foreach>";

echo Strings::InStr($str, "foreach") . "\n\n";

echo Strings::InStrRev($str, "foreach") . "\n\n";

echo StringHelpers::GetStackValue($str, ">", "<") . "\n\n";

?>