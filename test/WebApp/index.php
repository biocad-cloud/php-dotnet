<?php

include "../package.php";
include "App.php";

dotnet::Imports("System.Diagnostics.StackTrace");
dotnet::AutoLoad("etc/config.php");

Control::$debug = false;
Control::HandleRequest(new App());

?>