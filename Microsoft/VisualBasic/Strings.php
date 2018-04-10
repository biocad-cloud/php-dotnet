<?php

/* The strings module functions in VisualBasic language */

class Strings {
	
	// 进行非正则表达式的替换
	public static function Split($str, $deli) {
		$words = explode($deli, $str);
		return $words;
	}

	public static function Join($list, $deli) {
		return join($deli, $list);
	}
	
	// 如果查找不到字串在目标字符串之上的位置，则函数返回0
	// 假若能够查找得到，则会返回以1为准的位置
	public static function InStr($str, $find_subString, $begin = 0) {

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
			return ($pos + $begin + 1);
		}

	}

	public static function Mid($str, $start, $len) {
		return substr($str, $start, $len);
	}

	# Public Shared Function Replace(
	#     Expression As String, 
	#     Find As String, 
	#     Replacement As String, 
	#     Optional Start As Integer = 1, 
	#     Optional Count As Integer = -1, 
	#     Optional Compare As Microsoft.VisualBasic.CompareMethod = 0) As String

	/**
	 * Returns a string in which a specified substring has been replaced with another 
	 * substring a specified number of times.
	 *
	 * @param str: Required. String expression containing substring to replace.
	 * @param find: Substring being searched for.
	 * @param replacement: Required. Replacement substring.
	 * 
	 * @return string Replace returns the following values. If Replace returns Find is zero-length 
	 *                or Nothing Copy of Expression Replace is zero-length Copy of Expression with 
	 *                no occurrences of Find Expression is zero-length or Nothing, or Start is greater 
	 *                than length of Expression Nothing Count is 0 Copy of Expression
	 */
	public static function Replace($str, $find, $replacement) {
		return str_replace($find, $replacement, $str);
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