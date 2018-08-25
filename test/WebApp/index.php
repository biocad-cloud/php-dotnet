<?php

define("APP_DEBUG", true);
define("SITE_PATH", dirname(__FILE__));

include "../../package.php";
include "App.php";

Imports("System.Diagnostics.StackTrace");

dotnet::AutoLoad("etc/config.php");
dotnet::HandleRequest(new App());

class c extends controller {
	
    public function accessControl() {
        return true;
    }

    /**
     * 假若没有权限的话，会执行这个函数进行重定向
    */
    public function Redirect() {
        Redirect("/");
    }
}

?>