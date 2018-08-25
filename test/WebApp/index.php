<?php

define("APP_DEBUG", true);
define("SITE_PATH", dirname(__FILE__));

include "../../package.php";
include "App.php";
include "accessController.php";

Imports("System.Diagnostics.StackTrace");

dotnet::AutoLoad("etc/config.php");
dotnet::HandleRequest(new App(), new c());

?>