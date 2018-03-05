<?php

dotnet::Imports("Microsoft.VisualBasic.ApplicationServices.Debugger.Logging.LogEntry");
dotnet::Imports("Microsoft.VisualBasic.FileIO.FileSystem");

class LogFile {
	
	private $handle;
	
	public function __construct($path) {
		FileSystem::CreateDirectory(
		FileSystem::GetParentPath($path));

		$this->handle = $path;
	}
	
	/**
	 * set_error_handler(new LogFile("path/to/file.log")->LoggingHandler, E_ALL);
	 */
	public function LoggingHandler($errno, $errstr, $errfile, $errline) {
		// 创建logentry对象和logbody，然后将数据拓展进入目标日志文件之中
		$entry = new LogEntry();
		$log = "";
		
		echo $errno   . "\n";
		echo $errstr  . "\n";
		echo $errfile . "\n";
		echo $errline . "\n";

		FileSystem::WriteAllText($handle, $log, TRUE);
		
		/* Don't execute PHP internal error handler */
		return true;
	}
}

?>