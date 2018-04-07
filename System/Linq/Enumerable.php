<?php

class Enumerable {
	
	// 请注意这个函数并不会按照字典的key，只会按照value来排序
	public static function OrderBy($array, $key) {		
		$getKey =& $key;

		usort($array, function($a, $b) use (&$getKey) {
			$a = $getKey($a);
			$b = $getKey($b);
			
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

	public static function OrderByKey($array, $key) { 
		$keys   = array_keys($array);
		$keys   = Enumerable::OrderBy($keys, $key);
		$values = array();

		foreach ($keys as $key) {
			$values[$key] = $array[$key];
		}

		return $values;
	}		

	public static function OrderByKeyDescending($array, $key) { 
		return array_reverse(Enumerable::OrderByKey($array, $key));
	}

	public static function Select($table, $selector) {
		$projection = array();

		if (is_string($selector)) {
			foreach($table as $row) {
				array_push($projection, $row[$selector]);
			}
		} else {
			foreach($table as $row) {
				array_push($projection, $selector($row));
			}
		}

		return $projection;
	}

	public static function GroupBy($array, $key) {		
		$groups = array();
		$isKeyString = is_string($key);

		foreach($array as $row) {
			if ($isKeyString) {
				$keyValue = $row[$key];
			} else {
				$keyValue = $key($row);
			}			

			if (!array_key_exists($keyValue, $groups)) {
				$groups[$keyValue] = array();
			}

			array_push($groups[$keyValue], $row);
		}

		return $groups;
	}
}

?>