<?php

/**
 * 模拟VB.NET之中的字典对象
 */
class Dictionary {
	
	private $__data;
	
	function __construct(){
		$this->__data = array();
	}

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

	/**
	 * 将当前的这个字典对象序列化为json字符串，以返回给客户端浏览器
	 */
	public function GetJson() {
		return json_encode($this->__data);
	}
}

?>