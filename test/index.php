<?php

    include '../package.php';
	
	dotnet::Imports("System.Diagnostics.StackTrace");
	
	function stackTraceTest() {
			dotnet::ThrowException("test error message");
	}
	
	function __caller2() {
		stackTraceTest();
	}
	
	function __caller3() {
		__caller2();
	}
	
	function __initCaller() {
		__caller3();
	}
	
	__initCaller();
	
    
?>