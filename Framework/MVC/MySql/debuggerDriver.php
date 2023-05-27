<?php

namespace MVC\MySql {

	imports("Microsoft.VisualBasic.Strings");	
	imports("MVC.MySql.schemaDriver");
    imports("MVC.MySql.sqlDriver");
    
    /**
     * 这个模块并不执行mysql语句，而是将mysql语句显示出来
    */
    class MySqlDebugger extends sqlDriver implements ISqlDriver {

		private $buffer;

		/**
		 * @param resource $buffer 将SQL语句进行调试输出的句柄值，默认是将SQL语句打印在
		 *                         终端上面或者可以通过这个构造函数参数指定一个文件
		 * 
		 * @abstract 因为上层调用的表模型对象任然会需要schema信息来生成SQL语句，
		 *           所以这个调试器对象尽管并不执行SQL语句，但是仍然会需求数据库
		 *           连接参数来提供表结构信息
		*/
        function __construct(
            $database = "test", 
            $user     = NULL, 
            $password = NULL, 
            $host     = "localhost", 
            $port     = 3306, 
            $buffer   = NULL) {
                
			parent::__construct($database, $user, $password, $host, $port);
			
			if ($buffer) {
				$this->buffer = $buffer;
			} else {
				// 如果函数参数为空的话，默认是将调试数据打印在终端上面的
				$this->buffer = self::ConsoleBuffer();
			}			
		}

		/**
		 * 向一个文本文件输出文本数据
		*/
		public static function FileBuffer($filePath) {
			$parent = dirname($filePath);			
			mkdir($parent, 0777, true);
			return fopen($filePath, "w+");
		}

		/**
		 * 向终端输出文本数据
		*/
		public static function ConsoleBuffer() {
			return fopen('php://stdout', 'w');
		}

		public function getLastMySql() {
			return parent::getLastMySql();
		}

		public function ExecuteSql($SQL, $strict = true) {
			$this->last_mysql_expression = $SQL;
			fwrite($this->buffer, "$SQL\n");

			return null;
		}

		public function Fetch($SQL) {
			$this->last_mysql_expression = $SQL;
			fwrite($this->buffer, "$SQL\n");

			return null;
		}

		public function ExecuteScalar($SQL) {
			$this->last_mysql_expression = $SQL;
			fwrite($this->buffer, "$SQL\n");

			return null;
		}
    }
}
?>