<?php

dotnet::Imports("System.Collection.ICollection");

/**
 * A dynamics array object.
 */
class ArrayList extends ICollection {

    public function __construct() {
        parent::__construct();
    }

    public function Add($obj) {
        array_push($this->__data, $obj);
    }

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
}

?>
