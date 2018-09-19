<?php

	/**
	 * Get current year value
	 * 
	 * A full numeric representation of a year, 4 digits, by default
	 * 
	 * @param boolean $short A two digit representation of a year,
	 *      example as: 99 or 03
	 * 
	 * @example 1999 or 2003
	 * 
	 * @return string
	*/
	function year($short = false) {
		return date("Y");
	}

	/**
	 * Get current month value
	 * 
	 * Numeric representation of a month, with leading zeros, by default.
	 * 
	 * @param boolean $textual A short textual representation of a month, 
	 *      three letters, example as: Jan through Dec.
	 * 
	 * @example 01 through 12
	 * 
	 * @return string
	*/
	function month($textual = false) {
		if ($textual) {
			return date("M");
		} else {
			return date("m");
		}		
	}

?>