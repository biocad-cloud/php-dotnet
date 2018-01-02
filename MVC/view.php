<?php

dotnet::Imports("System.Diagnostics.StackTrace");
dotnet::Imports("System.Linq.Enumerable");

/*
 * html view handler
 */
class View {
	
	public static function Display($vars = NULL) {
		$name = StackTrace::GetCallerMethodName();		
		$path = "html/$name.html";
		$html = file_get_contents($path);		
		
		if ($vars) {
			echo $html;			
		} else {
			echo View::Assign($html, $vars);
		}			
	}
	
	public static function Assign($html, $vars) {
		
		# 在这里需要按照键名称长度倒叙排序，防止出现可能的错误替换
		$names = array_keys($vars);
		$names = Enumerable::OrderByDescending($vars, function($k) {
			return $k;
		});
		
		$reorder = array();
		
		foreach ($names as $key => $value) {
			$reorder[$key] = $value;
		}
		
		foreach ($reorder as $name => $value) {
			$html = str_replace($name, $value, $html);
		}
		
		return $html;
	}
}

?>