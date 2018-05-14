<?php

include "../../package.php";

dotnet::Imports("System.Diagnostics.StackTrace");
dotnet::AutoLoad("etc/config.php");

Imports("RFC7231.index");
Imports("Microsoft.VisualBasic.Conversion");

dotnet::HandleRequest(new App());

class App {
    
    public function notfound() {
        dotnet::PageNotFound("测试");
    }

    public function accessDenied() {
        dotnet::AccessDenied("测试");
    }

    public function internalErrors() {
        dotnet::ThrowException("测试");
    }

    public function index() {
        echo "<ul>
        <li><a href='errors.php?app=notfound'>not found</a></li>
        <li><a href='errors.php?app=accessDenied'>access denied</a></li>
        <li><a href='errors.php?app=internalErrors'>internal Errors</a></li>
        <li><a href='errors.php?app=custom&code=233&message=测试消息'>Custom:  &code=xxxx&message=XXXXXXX</a></li>
        </ul>";
    }

    public function custom() {
        $code = $_GET["code"];
        $code = Conversion::CInt($code);
        $msg  = $_GET["message"];

        RFC7231Error::Display($code, $msg);
    }
}
?>