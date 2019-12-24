<?php

imports("php.Utils");
imports("Microsoft.VisualBasic.Strings");

class LogEntry {
	
	public $errNO;
	public $errFile;
	public $errLine;
	public $format;

	public function __construct($no, $file, $line, $format = NULL) {
		$this->errNO   = $no;
		$this->errFile = $file;
		$this->errLine = $line;

		if (!$format) {
			$this->format = "[php::@no @time <code>@file</code>:line @line]<br />\n";
		} else {
			$this->format = $format;
		}
	}
	
	/**
	 * 按照指定的格式输出格式化的头部
	 */
	public function __toString() {		

		$out = $this->format;

		$out = Strings::Replace($out, "@time", Utils::Now(false));
		$out = Strings::Replace($out, "@no",   $this->errNO);		
		$out = Strings::Replace($out, "@line", $this->errLine);
		$out = Strings::Replace($out, "@file", $this->errFile); 

		return $out;
	}

	public function ToString() {
		return $this->__toString();
	}
}
?>