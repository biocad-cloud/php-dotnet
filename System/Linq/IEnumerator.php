<?php

Imports("System.Collection.ArrayList");

class IEnumerator {

    private $sequence;

    public function Count() {
        return count($this->sequence);
    }

    /**
     * @param array $source 
    */    
    public function __construct($source) {
        $this->sequence = $source;
    }

    public function Where($predicate) {

    }

    public function Select($project) {

    }

    public function GroupBy($keySelector) {

    }

    public function OrderBy($keySelector) {

    }

    public function OrderByDescending($keySelector) {

    }

    public function Take($n) {

    }

    public function Skip($n) {
        
    }

    public function SkipWhile($predicate) {

    }

    public function Distinct($keySelector) {

    }

    public function All($predicate) {

    }

    public function Any($predicate) {

    }

    public function SequenceEquals($another, $compares = null) {

    }

    public function Reverse() {

    }

    public function First() {

    }

    public function Last() {

    }

    public function Sum() {

    }

    public function Average() {

    }

    public function Min($keySelector = null) {

    }

    public function Max($keySelector = null) {

    }

    public function Aggregate($func) {

    }

    /**
     * Copies the elements of the System.Collections.ArrayList to a new System.Object array.
     * 
     * @return array An System.Object array containing copies of the elements of the 
     *               System.Collections.ArrayList.
    */
    public function ToArray() {        
        return (new ArrayObject($this->sequence))->getArrayCopy();
    }

    /**
     * Creates a System.Collections.Generic.List<T> from an System.Collections.Generic.IEnumerable<T>.
     * 
     * @return ArrayList A System.Collections.Generic.List<T> that contains elements from the input 
     *                   sequence.
    */
    public function ToList() {
        return new ArrayList($this->sequence);
    }
}
?>