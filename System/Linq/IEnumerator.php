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
     * Bypasses elements in a sequence as long as a specified condition is true and 
     * then returns the remaining elements.
     * 
     * @param function $predicate A function to test each element for a condition.
     * 
     * @return IEnumerator An ``System.Collections.Generic.IEnumerable<T>`` that contains 
     *                     the elements from the input sequence starting at the first 
     *                     element in the linear series that does not pass the test 
     *                     specified by predicate.
    */
    public function SkipWhile($predicate) {

    }

    /**
     * Generates a subset sequence of object within a specified range.
     * 
     * @param integer $start The value of the first integer in the sequence.
     * @param integer $count The number of sequential integers to generate.
     * 
     * @return IEnumerator
    */
    public function Range($start, $count) {
        return $this->Skip($start - 1)->Take($count);
    }

    /**
     * Returns distinct elements from a sequence by using a specified 
     * ``System.Collections.Generic.IEqualityComparer<T>`` to compare values.
     * 
     * @param function $keySelector An ``System.Collections.Generic.IEqualityComparer<T>``
     *                              to compare values.
     * 
     * @return IEnumerator An ``System.Collections.Generic.IEnumerable<T>`` that contains 
     *                     distinct elements from the source sequence.
    */
    public function Distinct($keySelector) {

    }

    /**
     * Determines whether all elements of a sequence satisfy a condition.
     *
     * @param function $predicate A function to test each element for a condition.
     * 
     * @return boolean true if every element of the source sequence passes the test 
     *                 in the specified predicate, or if the sequence is empty; 
     *                 otherwise, false.
    */
    public function All($predicate) {

    }

    /**
     * Determines whether any element of a sequence satisfies a condition.
     * 
     * @param function $predicate A function to test each element for a condition. 
     * 
     * @return boolean true if any elements in the source sequence pass the test in 
     *                 the specified predicate; otherwise, false.
    */
    public function Any($predicate) {

    }

    /**
     * Determines whether two sequences are equal by comparing their elements by using 
     * a specified ``System.Collections.Generic.IEqualityComparer<T>``.
     * 
     * @param array $another An ``System.Collections.Generic.IEnumerable<T>`` to compare 
     *                       to the first sequence.
     * @param function $compares An ``System.Collections.Generic.IEqualityComparer<T>`` to 
     *                           use to compare elements.
     * 
     * @return boolean true if the two source sequences are of equal length and their 
     *                 corresponding elements compare equal according to comparer; 
     *                 otherwise, false.
    */
    public function SequenceEquals($another, $compares = null) {

    }

    /**
     * Inverts the order of the elements in a sequence.
     * 
     * @return IEnumerator A sequence whose elements correspond to those of the 
     *                     input sequence in reverse order.
    */
    public function Reverse() {

    }

    public function First($predicate = null) {

    }

    public function Last($predicate = null) {

    }

    public function Sum($cast = null) {

    }

    public function Average($cast = null) {

    }

    public function Min($keySelector = null) {

    }

    public function Max($keySelector = null) {

    }

    /**
     * Applies an accumulator function over a sequence.
    */
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