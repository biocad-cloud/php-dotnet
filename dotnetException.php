<?php

class dotnetException extends Exception {
	
	public $stackTrace;
	public $message;
	
	function __constructor($message) {
		$this->message    = $message;
		$this->stackTrace = StackTrace::GetCallStack();
	}	
	
	public static function FormatOutput($message, $stackTrace) {
        $view = new StringBuilder();
		$view->AppendLine("<div class='dotnet-exception'>")
		     ->AppendLine("<p><span style='color:red'>" . $message . "</span>")
		     ->AppendLine("<p>")
		     ->AppendLine($stackTrace)
             ->AppendLine("</p>")
             ->AppendLine("</p>")
		     ->AppendLine("</div>");
		
		return $view->ToString();
	}
	
	public static function FormatExceptionOutput($ex) {
		return self::FormatOutput($ex->message, $ex->stackTrace);
	}
	
	public function __toString() {
		return self::FormatExceptionOutput(
			$this->message, 
			$this->stackTrace
		);
	}
}

?>