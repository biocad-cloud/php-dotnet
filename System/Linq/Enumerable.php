<?php

/**
 * Provides a set of static (Shared in Visual Basic) methods for 
 * querying objects that implement IEnumerable<T>.
 */
class Enumerable {

	/**
	 * Sorts the elements of a sequence in ascending order according to a key. 
	 * 
	 * @param array $array A sequence of values to order.
	 * @param function $key A function to extract a key from an element.
	 * 
	 * @return array An ``System.Linq.IOrderedEnumerable<T>`` whose elements are sorted according to a key.
	 * 
	 * @remarks 请注意这个函数并不会按照字典的key，只会按照value来排序
	*/
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
	
	/**
	 * Sorts the elements of a sequence in descending order according to a key.
	 * 
	 * @param array: A sequence of values to order.
	 * @param key: A function to extract a key from an element.
	 * 
	 * @return array An System.Linq.IOrderedEnumerable<T> whose elements are 
	 *               sorted in descending order according to a key.
	 * 
	 * @remarks: 对OrderBy的结果的逆序
	 */
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

	/**
	 * Projects each element of a sequence into a new form. 
	 * 
	 * @param table: A sequence of values to invoke a transform function on.
	 * @param selector: A transform function to apply to each element.
	 * 
	 * @return array An System.Collections.Generic.IEnumerable<T> whose elements 
	 *               are the result of invoking the transform function on each 
	 *               element of source.
	 */
	public static function Select($table, $selector) {
		$projection = array();

		if (is_string($selector)) {
			foreach($table as $row) {
				array_push($projection, $row[$selector]);
			}
		} else {
			$project = & $selector;

			foreach($table as $row) {
				array_push($projection, $project($row));
			}
		}

		return $projection;
	}

	/**
	 * Groups the elements of a sequence according to a specified key selector function and 
	 * creates a result value from each group and its key. The elements of each group are 
	 * projected by using a specified function. 
	 * 
	 * @param array: An System.Collections.Generic.IEnumerable<T> whose elements to group.
	 * @param key: A function to extract the key for each element.
	 * 
	 * @return array A collection of elements of type TResult where each element represents 
	 *               a projection over a group and its key.
	 */
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

	# System.Linq.Enumerable.ToDictionary<TSource, TKey, TElement>(
	# 	 this System.Collections.Generic.IEnumerable<TSource>, 
	#    System.Func<TSource,TKey>, 
	#    System.Func<TSource,TElement>
	# )

	/**
	 * Creates a System.Collections.Generic.Dictionary<T1,T2> from an 
	 * System.Collections.Generic.IEnumerable<T> according to specified 
	 * key selector and element selector functions.
	 * 
	 * @param source: An System.Collections.Generic.IEnumerable<T> to create 
	 *                a System.Collections.Generic.Dictionary<T1,T2> from.
	 * @param key: A function to extract a key from each element.
	 * @param element: A transform function to produce a result element 
	 *                 value from each element.
	 * 
	 * @return array A System.Collections.Generic.Dictionary<T1,T2> that contains 
	 *               values of type TElement selected from the input sequence.
	 */
	public static function ToDictionary($source, $key, $element = NULL) {
		$table           = array();
		$elementSelector = $element;
		$keySelector     = $key;

		if (!$element) {
			$element = function($obj) {
				return $obj;
			};
		} else if (is_string($element)) {
			$element = function($obj) use ($elementSelector) {
				return $obj[$elementSelector];
			};
		}

		if (!$key) {
			throw new exception("The key selector can not be NULL!");
		} else if (is_string($key)) {
			$key = function($obj) use ($keySelector) {
				return $obj[$keySelector];
			};
		}

		foreach ($source as $obj) {
			$table[$key($obj)] = $element($obj);
		}

		return $table;
	}

	/**
	 * Returns the last element of a sequence.
	 * 
	 * @param array $source An System.Collections.Generic.IEnumerable<T> to return the last element of.
	 *
	 * @return mixed The value at the last position in the source sequence.
	*/
	public static function Last($source) {
		return end($source);
	}
}

?>