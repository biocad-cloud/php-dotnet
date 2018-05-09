<?php

/** 
 * The strings module functions in VisualBasic language. The Strings module contains 
 * procedures used to perform string operations.
*/
class Strings {
	
	/**
	 * Returns a zero-based, one-dimensional array containing a specified number of substrings.
	 * 
	 * @param string $str Required. String expression containing substrings and delimiters.
	 * @param string $deli Optional. Any single character used to identify substring limits. 
	 * 					   If Delimiter is omitted, the space character (" ") is assumed to 
	 * 					   be the delimiter.
	 * 
	 * @return array String array. If Expression is a zero-length string (""), Split returns a 
	 * 			     single-element array containing a zero-length string. If Delimiter is a 
	 * 				 zero-length string, or if it does not appear anywhere in Expression, Split 
	 * 				 returns a single-element array containing the entire Expression string.
	*/
	public static function Split($str, $deli = " ") {
		$words = explode($deli, $str);
		return $words;
	}

	/**
	 * Returns a string created by joining a number of substrings contained in an array.
	 * 
	 * @param array $list Required. One-dimensional array containing substrings to be joined.
	 * @param string $deli Optional. Any string, used to separate the substrings in the returned string. 
	 * 								 If omitted, the space character (" ") is used. If Delimiter is a 
	 * 								 zero-length string ("") or Nothing, all items in the list are 
	 * 								 concatenated with no delimiters.
	 * 
	 * @return string Returns a string created by joining a number of substrings contained in an array.
	*/
	public static function Join($list, $deli = " ") {
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

	/**
	 * Returns a string that contains a specified number of characters starting from a specified position in a string.
	 * 对目标字符串进行取子字符串的操作，请注意这个函数的下标是从1开始的。
	 * 
	 * @param string $str Required. String expression from which characters are returned. 目标字符串
	 * @param string $start Required. Integer expression. Starting position of the characters to return. If Start is 
	 *                      greater than the number of characters in str, the Mid function returns a zero-length 
	 *                      string (""). Start is one based.
	 * @param string $len Optional. Integer expression. Number of characters to return. If omitted or if there are fewer 
	 * 					  than Length characters in the text (including the character at position Start), all characters 
	 *                    from the start position to the end of the string are returned.
	 * 
	 * @return string A string that consists of the specified number of characters starting from the specified position 
	 *                in the string.
	*/
	public static function Mid($str, $start, $len = -1) {
		if ($len > 0) {
			return substr($str, $start, $len);
		} else {
			
		}
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
	 * substring a specified number of times. 进行非正则表达式的替换
	 *
	 * @param string $str: Required. String expression containing substring to replace.
	 * @param string $find: Substring being searched for.
	 * @param string $replacement: Required. Replacement substring.
	 * 
	 * @return string Replace returns the following values. If Replace returns Find is zero-length 
	 *                or Nothing Copy of Expression Replace is zero-length Copy of Expression with 
	 *                no occurrences of Find Expression is zero-length or Nothing, or Start is greater 
	 *                than length of Expression Nothing Count is 0 Copy of Expression
	*/
	public static function Replace($str, $find, $replacement) {
		return str_replace($find, $replacement, $str);
	}

	/**
	 * 将字符串转换为大写形式 
	*/
	public static function UCase($str) {
		return strtoupper($str);
	}

	/**
	 * 将字符串转换为小写形式
	*/
	public static function LCase($str) {
		return strtolower($str);
	}

	/**
	 * 将字符串之中的字符的顺序进行反转，然后返回新的字符串
	*/
	public static function Reverse($str) {
		return strrev($str);
	}

	public static function StartWith($haystack, $needle) {
    	$length = strlen($needle);
    	return (substr($haystack, 0, $length) === $needle);
	}

	public function EndWith($haystack, $needle) {
    	$length = strlen($needle);
    	return $length === 0 || (substr($haystack, -$length) === $needle);
	}
}

?>