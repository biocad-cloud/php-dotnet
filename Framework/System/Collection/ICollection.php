<?php 

Imports("Microsoft.VisualBasic.Data.csv.Extensions");

/**
 * Defines size, enumerators, and synchronization methods for all nongeneric collections.
 * 对集合类型的基本抽象
*/
abstract class ICollection implements ArrayAccess {

    /**
     * 数组是序列对象的最基本数据存储结构对象
     * 
     * @var array
    */
    protected $__data;
    
    /** 
     * 函数返回当前的这个序列对象之中的元素数量
     * 
     * @return integer 序列之中的元素的数量
    */
    public function count() {
        return \count($this->__data);
    }

    /** 
     * 返回序列之中的最后一个元素的值
    */
    public function Last() {
        return end($this->__data);
    }

    function __get($name) {
        if($name === 'Count')
            return $this->count($this->__data);
        user_error("Invalid property: " . __CLASS__ . "->$name");
    }
    function __set($name, $value) {
        user_error("Can't set property: " . __CLASS__ . "->$name");
    }

    /**
     * 使用默认的构造函数
    */
    function __construct($source = NULL) {
        $this->__data = $source;
        
        if (empty($this->__data)) {
            $this->__data = [];
        }
	}

    /**
     * 这个函数定义当前的这个集合对象与字符串函数交互的默认行为
    */
    public function __toString() {
        return $this->GetJson();
    }

    /**
	 * 将当前的这个字典对象序列化为json字符串，以返回给客户端浏览器
	*/
	public function GetJson() {
		return json_encode($this->__data);
    }

    #region "implements ArrayAccess"

    public function offsetSet($offset, $value) {
        $this->__data[$offset] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->__data[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->__data[$offset]);
    }

    public function offsetGet($offset) {
        if (isset($this->__data[$offset])) {
           return $this->__data[$offset];
        } else {
           return null;
        }
    }

    #endregion

    /**
     * Save this data collection as csv file
     * 
     * @param string $path The csv file path for save this collection object.
     * @param string $encoding The text file content encoding, by default is utf8 encoding. 
     * @param array $project The csv file header mapping.
     * 
     * @return boolean
    */
    public function SaveTo($path, $project = null, $encoding = "utf8") {
        return Microsoft\VisualBasic\Data\csv\Extensions::SaveTo(
            $this->__data, $path, 
            $project, 
            $encoding
        );
    }

    /**
     * Copies the elements of the ``System.Collections.ArrayList`` 
     * to a new System.Object array.
     * 
     * @return array An ``System.Object`` array containing copies of the 
     *    elements of the ``System.Collections.ArrayList``.
    */
    public function ToArray() {
        return (new ArrayObject($this->__data))->getArrayCopy();
    }
}

?>