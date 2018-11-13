<?php

Imports("System.Collection.ICollection");

/**
 * A dynamics array object. Implements the ``System.Collections.IList`` interface 
 * using an array whose size is dynamically increased as required. To browse the 
 * ``.NET Framework`` source code for this type, see the Reference Source.
 * 
 * (php的字典是类似于字典一样的数据类型，即使是数字类型的索引，在删除了某一个索引之后，
 * 后面的索引键的顺序也不会向前移动。
 * 这个列表对象是单纯的以数字作为索引，如果删除或者插入元素的话，在变化的位点之后的所有元素的
 * 键值对关系都会被改变。
 * 请注意，在这个对象之中**下标一定是从零开始连续递增的**)
*/
class ArrayList extends ICollection {

    public function __construct($source = null) {
        parent::__construct($source);
    }

    /**
     * Adds an object to the end of the ``System.Collections.ArrayList``.
     *
     * @param mixed $obj The System.Object to be added to the end of the 
     * ``System.Collections.ArrayList``. The value can be null.
     * 
     * @return integer The ``System.Collections.ArrayList`` index at which 
     * the value has been added.
    */
    public function Add($obj) {
        array_push($this->__data, $obj);
        return count($this->__data) - 1;
    }

    /**
     * Adds the elements of an ``System.Collections.ICollection`` to the end of the 
     * ``System.Collections.ArrayList``.
     * 
     * @param array $array The System.Collections.ICollection whose elements should be 
     *                     added to the end of the System.Collections.ArrayList. The 
     *                     collection itself cannot be null, but it can contain elements 
     *                     that are null.
    */
    public function AddRange($array) {
        foreach($array as $obj) {
            $this->Add($obj);
        }
    }

    /**
     * pops and returns the last value of the list, shortening 
     * the list by one element.
    */
    public function RemoveLast() {
        return array_pop($this->__data);
    }

    public function Remove($obj) {
        die(dotnet::MethodNotImplemented);
    }

    /** 
     * 将某一个索引号对应的元素删除
     * 
     * > 目标索引号后面的元素和键之间的键值对的对应关系将会发生改变
     * 
     * @return mixed 函数返回被删除的元素对象
    */
    public function RemoveAt(int $index) {
        $x = $this->__data[$index];
        $n = $this->count();

        # 进行元素删除
        # 只需要将后一个元素值覆盖掉前一个元素的值，然后删除最后一个元素即可
        for ($i = $index; $i < $n - 1; $i++) {
            # 取得下一个元素的数据
            $next = $this->__data[$i + 1];
            # 进行对前一个元素的值的覆盖操作
            $this->__data[$i] = $next;
        }

        # 删除最后一个元素
        unset($this->__data[$n - 1]);

        return $x;
    }

    /** 
     * @param integer $index 需要插入新的数据的索引
     * @param mixed $obj 所将要被插入的值
     * 
     * @return ArrayList 函数返回当前的列表对象以构成链式调用
    */
    public function InsertAt(int $index, mixed $obj) {
        # 首先列表长度增加一个空元素
        # 然后前一个元素覆盖后一个元素的值
        # 直到index位置被空下来，之后填入obj值即可完成插入操作

        # 首先执行向后位移填充操作
        for($i = $this->count(); $i > $index; $i--) {
            $previous = $this->__data[$i - 1];
            $this->__data[$i] = $previous;
        }

        # 然后再index填入值即可
        $this->__data[$index] = $obj;
        return $this;
    }
}

?>
