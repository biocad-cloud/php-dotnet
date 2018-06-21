<?php

Imports("System.Collection.ArrayList");

/**
 * Supports a simple iteration over a non-generic collection. And 
 * Provides a set of static (Shared in Visual Basic) methods for 
 * querying objects that implement 
 * ``System.Collections.Generic.IEnumerable<T>``.
*/
class IEnumerator {

    /**
     * Array
    */
    private $sequence;

    /**
     * Returns the number of elements in a sequence. Or returns a number that 
     * represents how many elements in the specified sequence satisfy a 
     * condition if the ``assert`` is not null.
    */
    public function Count($assert = null) {
        if ($assert) {
            return $this->Where($assert)->Count();
        } else {
            return count($this->sequence);
        }        
    }

    /**
     * @param array $source 
    */    
    public function __construct($source) {
        $this->sequence = $source;
    }

    /**
     * @return IEnumerator
    */
    public function Where($predicate) {

    }

    /**
     * @return IEnumerator
    */
    public function Select($project) {

    }

    /**
     * @return IEnumerator
    */
    public function GroupBy($keySelector) {

    }

    /**
     * @return IEnumerator
    */
    public function OrderBy($keySelector) {

    }

    /**
     * @return IEnumerator
    */
    public function OrderByDescending($keySelector) {

    }

    /**
     * @return IEnumerator
    */
    public function Take($n) {

    }

    /**
     * @return IEnumerator
    */
    public function Skip($n) {
        
    }

    /**
     * @return IEnumerator
    */
    public function SkipWhile($predicate) {

    }

    /**
     * @return IEnumerator
    */
    public function Distinct($keySelector) {

    }

    /**
     * @return boolean
    */
    public function All($predicate) {

    }

    /**
     * @return boolean
    */
    public function Any($predicate) {

    }

    /**
     * @return boolean
    */
    public function SequenceEquals($another, $compares = null) {

    }

    /**
     * @return IEnumerator
    */
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