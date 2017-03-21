<?php 

/**
 *  对集合类型的基本抽象
 */
abstract class ICollection {

    protected $__data;
	
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
    function __construct(){
		$this->__data = array();
	}

    // /**
    //  * 使用已经存在的array数组数据构建一个集合对象类型
    //  */
    // function __construct($source){
    //     $this->__data = $source;
    // }

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
}

?>