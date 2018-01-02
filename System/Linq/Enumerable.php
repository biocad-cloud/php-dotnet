<?php

class Enumerable {
	
	public static function OrderBy($array, $key) {			
		usort($array, function($a, $b) {
			$a = $key($a);
			$b = $key($b);
			
			if ($a == $b) {
				return 0;				
			} else {
				return ($a < $b) ? -1 : 1;
			}			
		});
		
		return $array;
	}
	
	public static function OrderByDescending($array, $key) {
		return array_reverse(Enumerable::OrderBy($array, $key));		
	}
}

?>