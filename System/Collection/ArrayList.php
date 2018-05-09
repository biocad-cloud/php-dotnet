<?php

dotnet::Imports("System.Collection.ICollection");

/**
 * A dynamics array object. Implements the System.Collections.IList interface using 
 * an array whose size is dynamically increased as required.To browse the .NET 
 * Framework source code for this type, see the Reference Source.
*/
class ArrayList extends ICollection {

    public function __construct() {
        $this->__data = array();
    }

    /**
     * Adds an object to the end of the System.Collections.ArrayList.
     *
     * @param mix $obj The System.Object to be added to the end of the System.Collections.ArrayList. The value can be null.
     *
     * @return integer The System.Collections.ArrayList index at which the value has been added.
    */
    public function Add($obj) {
        array_push($this->__data, $obj);
        return count($this->__data) - 1;
    }

    /**
     * Adds the elements of an System.Collections.ICollection to the end of the 
     * System.Collections.ArrayList.
     * 
     * @param array $array The System.Collections.ICollection whose elements should be 
     *                     added to the end of the System.Collections.ArrayList. The 
     *                     collection itself cannot be null, but it can contain elements 
     *                     that are null.
     * 
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

    public function RemoveAt(int $index) {
        die(dotnet::MethodNotImplemented);
    }

    public function InsertAt(int $index, $obj) {
        die(dotnet::MethodNotImplemented);
    }

    /**
     * Copies the elements of the System.Collections.ArrayList to a new System.Object array.
     * 
     * @return array An System.Object array containing copies of the elements of the 
     *               System.Collections.ArrayList.
    */
    public function ToArray() {        
        return (new ArrayObject($this->__data))->getArrayCopy();
    }
}

?>
