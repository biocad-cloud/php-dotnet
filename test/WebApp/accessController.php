<?php

Imports("MVC.controller");

class c extends controller {
	
    public function accessControl() {
        return true;
    }

    /**
     * 假若没有权限的话，会执行这个函数进行重定向
    */
    public function Redirect($code) {
        Redirect("/");
    }
}

?>