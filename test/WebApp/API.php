<?php

include "../package.php";
include "REST.php";

dotnet::$debug = false;

imports("System.Diagnostics.StackTrace");

dotnet::AutoLoad("etc/config.php");

Control::$debug = false;
Control::HandleRequest(new REST());
?>