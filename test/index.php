<?php

    // include '../package.php';
	include '../System/Diagnostics/StackTrace.php';
	
	function stackTraceTest() {
			echo StackTrace::GetCallStack();
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
	
    // dotnet::ThrowException("test error message");
?>