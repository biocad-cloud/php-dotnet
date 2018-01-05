<?php

include "../package.php";
include "REST.php";

dotnet::ShowAllMessage();
dotnet::$debug = false;

dotnet::Imports("System.Diagnostics.StackTrace");
dotnet::AutoLoad("etc/config.php");

Control::$debug = false;
Control::HandleRequest(new REST());
?>