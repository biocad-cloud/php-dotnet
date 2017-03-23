<?php

/* The strings module functions in VisualBasic language */

class Strings {
	
	public static function Split($str, $deli) {
		
		// split the phrase by any number of commas or space characters,
		// which include " ", \r, \t, \n and \f
		$words = preg_split("/" . $deli . "/", $str);
		return $words;

	}
	
	// 如果查找不到字串在目标字符串之上的位置，则函数返回0
	// 假若能够查找得到，则会返回以1为准的位置
	public static function InStr(string $str, string $find_subString, int $begin) {

		$pos = strpos($str, $find_subString, $begin);

		// Note our use of ===.  Simply == would not work as expected
		// because the position of 'a' was the 0th (first) character.
		if ($pos === false) {

    		// echo "The string '$findme' was not found in the string '$mystring'";
			// not found, return ZERO in visualbasic
			return 0;

		} else {

    		// echo "The string '$findme' was found in the string '$mystring'";
    		// echo " and exists at position $pos";

			// found sub string, returns the 1 base position
			return ($p + $begin + 1);
		}

	}

	public static function Mid($str, $start, $len) {
		return substr($str, $start, $len);
	}

	public static function Replace($str, $find, $replacement) {

	}

	public static function UCase($str) {
		return strtoupper($str);
	}

	public static function LCase($str) {
		return strtolower($str);
	}

	public static function Reverse($str) {
		return strrev($str);
	}
}

?>