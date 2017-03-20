<?php

require_once("../ICollection.php")

/**
 * 模拟VB.NET之中的字典对象
 */
class Dictionary extends ICollection {
	
	public function Keys() {
		return array_keys($this->__data);
	}

	public function Values() {
		return array_values($this->__data);
	}

	/**
	 * 使用指定的键名获取字典之中的相对应的值
	 */
	public function Item($key) {
		return $this->__data[$key];
	}

	/**
	 * 判断指定的键名是否存在于当前的字典对象之中
	 */
	public function ContainsKey($key) {
		return array_key_exists($key, $this->__data);
	}

	/**
	 * 向当前的字典对象之中添加一个键值对
	 */
	public function Add($key, $value) {
		$this->__data[$key] = $value;
	}
}

?>