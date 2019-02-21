<?php

/** 
 * 具有和.Net Framework类似的栈信息输出的错误消息对象
*/
class dotnetException extends Exception {
	
	/** 
	 * 栈信息
	 * 
	 * @var StackTrace
	*/
	public $stackTrace;
		
	function __constructor($message, $code = -1) {
		parent::__construct ($message, $code);
		$this->stackTrace = StackTrace::GetCallStack();
	}	
	
	/** 
	 * 按照.NET框架的错误形式进行格式化输出
	 * 
	 * @param string $message
	 * @param StackTrace $stackTrace
	*/
	public static function FormatOutput($message, $stackTrace) {
        $view = new StringBuilder();
		$view->AppendLine("<div class='dotnet-exception'>")
			 ->AppendLine("<p>
				 <span style='color:red'>
					 <blockquote>$message</blockquote>
				 </span>")
		     ->AppendLine("<p>")
		     ->AppendLine($stackTrace->ToString())
             ->AppendLine("</p>")
             ->AppendLine("</p>")
		     ->AppendLine("</div>");
		
		return $view->ToString();
	}
	
	/**
	 * @param dotnetException $ex 所有继承自这个异常对象的错误都可以用这个函数进行格式化输出
	*/
	public static function FormatExceptionOutput($ex) {
		return self::FormatOutput($ex->message, $ex->stackTrace);
	}
	
	public function __toString() {
		if (empty($this->stackTrace)) {
			return $this->message;
		} else {
			return self::FormatOutput(
				$this->message, 
				$this->stackTrace
			);
		}
	}
}