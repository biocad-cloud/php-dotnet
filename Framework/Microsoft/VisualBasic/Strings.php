<?php

/** 
 * The strings module functions in ``VisualBasic`` language. The Strings module contains 
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
		if (empty($list)) {
			# 20180822 如果参数数组是空的话，会出现php警告
			# join(): Invalid arguments passed 
			# 在这里判断一下
			return "";
		} else {
			return join($deli, $list);
		}		
	}
	
	public static function Unique($strings) {
		$distinct = [];

		foreach($strings as $key) {
			if (!array_key_exists($key, $strings)) {
				$distinct[$key] = true;
			}
		}

		return array_keys($distinct);
	}

	/**
	 * Returns an integer specifying the start position of the first occurrence of one 
	 * string within another.
	 * 
	 * 如果查找不到字串在目标字符串之上的位置，则函数返回0
	 * 假若能够查找得到，则会返回以1为准的位置
	 * 
	 * @param string $str Required. String expression being searched.
	 * @param string $find_subString Required. String expression sought.
	 * @param integer $begin 
	 * 
	 * @return integer Returns an integer specifying the start position of the first 
	 *                 occurrence of one string within another.
	*/
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

	# Microsoft.VisualBasic.Strings.InStrRev(string, string, int, Microsoft.VisualBasic.CompareMethod)

	/**
	 * Returns the position of the first occurrence of one string within another, 
	 * starting from the right side of the string.
	 * 
	 * 这个函数返回来的位置是从下标1开始的
	 * 
	 * @param string $str Required. String expression being searched.
	 * @param string $find_subString Required. String expression being searched for.
	 * 
	 * @return integer Returns the position of the first occurrence of one string within another, 
	 *                 starting from the right side of the string.
	*/
	public static function InStrRev($str, $find_subString) {
		return strrpos($str, $find_subString) + 1;
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
		if ($start > self::Len($str)) {
			return "";
		}

		if ($len > 0) {
			return substr($str, $start - 1, $len);
		} else {
			return substr($str, $start - 1);
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
		if (is_array($str)) {
			throw new Exception("str is array!");
		}
		if (is_array($find)) {
			throw new Exception("find is array!");
		}
		if (is_array($replacement)) {
			throw new Exception("replacement is array!");
		}
		return str_replace($find, $replacement, $str);
	}

	public static function CharAt($string, $i) {
		if (empty($string)) {
			return "";
		}

		if ($i < 0) {
			$i = strlen($string) + $i;
		}

		if ($i >= strlen($string) || $i < 0) {
			return "";
		}

		return $string[$i];
	}

	/**
	 * Returns an integer containing either the number of characters in a string 
	 * or the element counts of the target array.
	 * 
	 * @param string|array $obj string or array
	 * 
	 * @return integer length
	*/
	public static function Len($obj) {
		if (is_string($obj)) {
			return strlen($obj);
		} else if (is_array($obj)) {
			return count($obj);
		} else {
			throw new exception("Invalid data type!");
		}
	}

	public static function Empty($str, $stringAsFactor = false) {
		# 2018-7-19
		# 在php之中会将字符串0也作为空值，这是一个bug？？
		#
		# echo "<?php echo var_dump(empty('0'));" | php
		# bool(true)

		# 在这里额外的处理一下0字符串的特殊情况
		if (empty($str) && $str != "0") {
			return true;
		} else if (Strings::Len($str) == 0) {
			return true;
		} else if ($stringAsFactor) {
			return $str == "null"      || 
				   $str == "NULL"      || 
				   $str == "undefined" || 
				   $str == "undefine";
		} else {
			return false;
		}
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

	public static function StartWith($haystack, $needle, $caseSensitive = TRUE) {
    	$length = strlen($needle);
    	return (substr($haystack, 0, $length) === $needle);
	}

	/**
	 * String haystack is end with needle?
	 * 
	 * @param bool $caseSensitive 默认是大小写敏感的
	 * 
	 * @return bool
	*/
	public static function EndWith($haystack, $needle, $caseSensitive = TRUE) {
		$length = strlen($needle);
		
		if ($length === 0) {
			return true;
		} else {
			$a = substr($haystack, -$length);
			$b = $needle;

			if (!$caseSensitive) {
				$a = strtolower($a);
				$b = strtolower($b);
			}

			return ( $a === $b );
		} 
	}
}

?>