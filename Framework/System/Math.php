<?php

namespace System;

/**
 * Provides constants and static methods for trigonometric, 
 * logarithmic, and other common mathematical functions.
*/
class Math {

    /** 
     * Represents the natural logarithmic base, specified by the constant, ``e``.
     * 
     * @var double
    */
    const E = 2.718281828459045;

    /** 
     * Represents the ratio of the circumference of a circle to its diameter, 
     * specified by the constant, ``π``.
     * 
     * @var double 
    */
    const PI = 3.14159265358979323846;

    /** 
     * The Math.LOG2E property represents the base 2 logarithm of e, 
     * approximately 1.442
     * 
     * @var double
    */
    const LOG2E = 1.4426950408889634;

    /** 
     * The Math.LOG10E property represents the base 10 logarithm of e, 
     * approximately 0.434
     * 
     * @var double
    */
    const LOG10E = 0.4342944819032518;

    /** 
     * The Math.SQRT1_2 property represents the square root of 1/2 
     * which is approximately 0.707
     * 
     * @var double
    */
    const SQRT1_2 = 0.7071067811865476;

    /** 
     * The Math.SQRT2 property represents the square root of 2, 
     * approximately 1.414
     * 
     * @var double
    */
    const SQRT2 = 1.4142135623730951;

    /** 
     * The Math.LN2 property represents the natural logarithm of 2, 
     * approximately 0.693
     * 
     * @var double
    */
    const LN2 = 0.6931471805599453;

    /** 
     * The Math.LN10 property represents the natural logarithm of 10, 
     * approximately 2.302
     * 
     * @var double 
    */
    const LN10 = 2.302585092994046;

    /** 
     * The Math.trunc() function returns the integer part of a number by 
     * removing any fractional digits.
     * 
     * @param double $x
     * @return integer
    */
    public static function trunc($x) {
        $text = \strval($x);
        $text = \explode(".", $text);
        $text = $text[0];

        return \intval($text);
    }

    /** 
     * The Math.sign() function returns the sign of a number, indicating whether 
     * the number is positive, negative or zero.
     * 
     * @param double $x
     * @return integer
    */
    public static function sign($x) {
        if ($x > 0) {
            return 1;
        } else if ($x < 0) {
            return -1;
        } else {
            return 0;
        }
    }

    /** 
     * The Math.log2() function returns the base 2 logarithm of a number
    */
    public static function log2($x) {
        return \log($x, 2);
    }

    /** 
     * The Math.cbrt() function returns the cube root of a number
    */
    public static function cbrt($x) {
        return $x ** (1 / 3);
    }
}