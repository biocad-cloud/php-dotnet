<?php

imports("System.Collection.ArrayList");

/** 
 * The unique id generator.
*/
class Uid {
	
	/** 
	 * @var ArrayList
	*/
	private $chars;
	/** 
	 * @var integer
	*/
	private $value = 0;
	/** 
	 * @var string[];
	*/
	private $charArray;
	/** 
	 * @var integer
	*/
	private $upbounds;

	const Digits = "0123456789";
	const AlphabetLCase = "abcdefghijklmnopqrstuvwxyz";
	const AlphabetUCase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

	public function __construct($n = 0, $caseSensitive = True) {
		if ($caseSensitive) {
			$this->charArray = self::Digits . 
							   self::AlphabetLCase . 
							   self::AlphabetUCase; 
		} else {
			$this->charArray = self::Digits . self::AlphabetLCase; 
		}

		$this->charArray = str_split($this->charArray);
		$this->upbounds  = count($this->charArray) - 1;
		$this->chars     = new ArrayList();
		$this->chars->Add(0);

		if ($n > 0) {
			$this->Add($n);
		}
	}

	/** 
	 * @param integer $n
	 * @return Uid
	*/
	public function Add($n = 1) {
		for($i = 0; $i < $n; $i++) {
			$this->plus(count($this->chars) - 1);
		}

		return $this;
	} 

	/** 
	 * @param integer $l
	 * @return integer move
	*/
	private function plus($l) {
		$n    = $this->chars[$l] + 1;
		$move = 0;

		if ($n > $this->upbounds) {
			$n  = 0;
			$pl = $l - 1;

			if ($pl < 0) {
				$this->chars->InsertAt(0, 1);
				$l++;
				$move = 1;
			} else {
				$l += $this->plus($pl);
			}
		}

		$this->chars[$l] = $n;
		$this->value++;

		return $move;
	}

	/** 
	 * Convert the ``integer`` uid to string value.
	 * 
	 * @return string
	*/
	public function ToString() {
		$chars = [];

		foreach($this->chars->ToArray() as $i) {
			array_push($chars, $this->charArray[$i]);
		}

		return implode("", $chars);
	}
}