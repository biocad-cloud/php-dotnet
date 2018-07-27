<?php

namespace MVC\MySql {

    Imports("Microsoft.VisualBasic.Strings");

    /**
     * MySql execute or sql model debugger
    */ 
    class sqlDriver {
		
		#region "MySql connection info"

		/**
		 * 数据库的名称
		 * 
		 * @var string
		*/
		public $database;

		private $user;
		private $password;
		private $host;
		private $port;

		#endregion

		/**
		 * 当前的这个表模型对象实例的最后一条执行的MySql语句
		 * 
		 * @var string
		*/
		protected $last_mysql_expression;

		function __construct($database, $user, $password, $host = "localhost", $port = 3306) {
			$this->database = $database;
			$this->user     = $user;
			$this->password = $password;
			$this->host     = $host;
			$this->port     = $port;
		}
		
		/**
		 * @return string
		*/
        public function GetDatabaseName() {
            return $this->database;
        }

		/**
		 * 显示mysql表的结构
		 * 
		 * @example
		 * 
		 *    DESCRIBE TableName
		 * 
		 * @param string $tableName The table name for get schema structure info.
		*/
		public function Describe($tableName) {
			$db   = $this->database;
			$SQL  = "DESCRIBE `$db`.`$tableName`;";
			$link = $this->__init_MySql();   
			
			mysqli_select_db($link, $db); 
			mysqli_query($link, "SET names 'utf8'");			      

			$schema = mysqli_query($link, $SQL);

			if (empty($schema)) {
				$message = "Database query error for table schema: $tableName.\n\n";
				$message = $message . "<code>$SQL</code>";
				$message = $message . "Connection Info: \n\n";
				$message = $message . \json_encode([
					"host"     => $this->host, 
					"port"     => $this->port, 
					"database" => $this->database, 
					"user"     => $this->user, 
					"password" => $this->password
				]); 

				# throw new \dotnetException($message);
				\dotnet::ThrowException($message);
			}

			mysqli_close($link);

			return $schema;
		}		
		
		/**
		 * 使用这个函数来打开和mysql数据库的链接
		 * 
		 * @return mysqli 返回数据库的链接
		*/
		protected function __init_MySql() {	
			$link = mysqli_connect(
				$this->host,   
				$this->user,
				$this->password, 
				$this->database, 
				$this->port
			);
						
			if (false == $link) {
				$msg = "Error: Unable to connect to MySQL." . PHP_EOL;
				$msg = "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
				$msg = "Debugging error: " . mysqli_connect_error() . PHP_EOL;
					
				\dotnet::ThrowException($msg);

			} else {
				return $link;
			}
		}

		/**
		 * Get the last executed sql expression string value.
		 * 
		 * @return string The last executed sql expression.
		*/
		public function getLastMySql() {
			return $this->last_mysql_expression;
		}
    }

    /**
     * MySql执行器和调试器所共有的一些简化的接口函数集合
    */
	interface ISqlDriver {
		public function getLastMySql();
		public function ExecuteSql($SQL);
		public function Fetch($SQL);
		public function ExecuteScalar($SQL); 
	}
}

?>