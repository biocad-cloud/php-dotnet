<?php 

imports("System.Collection.ICollection");

/**
 * Represents a first-in, first-out collection of objects.
 */
class Queue extends ICollection {

    /**
     * @param array $seq The initial data sequence.
    */
    public function __construct($seq = null) {
        parent::__construct($seq);
    }

    /**
     * Removes all of the contents in current queue sequence.
    */
    public function clear() {
        $this->__data = [];
    }

    /**
     * This queue has no element?
    */
    public function isEmpty() {
        return empty($this->__data) || count($this->__data) == 0;
    }

    /**
     * Returns the value at the front of the queue
    */
    public function Peek() {
        if ($this->isEmpty()) {
            return null;
        } else {
            return $this->__data[0];
        }
    }

    /**
     * Removes and returns the value at the front of the queue
    */
    public function Pop() {
        if ($this->isEmpty()) {
            return null;
        } else {
            return array_shift($this->__data);
        }
    }

    /**
     * Pushes values into the queue
    */
    public function Push(...$values) {
        foreach($values as $x) {
            array_push($this->__data, $x);
        }
    }
}

?>