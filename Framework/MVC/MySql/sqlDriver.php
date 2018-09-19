<?php

namespace MVC\MySql {

	Imports("System.Text.StringBuilder");
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
		
		/**
		 * Mysql链接的缓存池
		 * 
		 * @var array
		*/
		static $mysqliCache = [];

		/**
		 * 在这个构造函数之中传递构建mysqllink所必须的参数
		*/
		function __construct($database, $user, $password, $host = "localhost", $port = 3306) {
			$this->database = $database;
			$this->user     = $user;
			$this->password = $password;
			$this->host     = $host;
			$this->port     = $port;
		}
		
		/**
		 * 得到所连接的数据库的名称
		 * 
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
		 * 
		 * @return array
		*/
		public function Describe($tableName) {
			$db   = $this->database;
			$SQL  = "DESCRIBE `$db`.`$tableName`;";
			$link = $this->__init_MySql(false);   
			
			\mysqli_select_db($link, $db); 
			\mysqli_query($link, "SET names 'utf8'");			      

			$schema = \mysqli_query($link, $SQL);

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

			# 2018-08-21 在这里是将打开的mysql链接加入到缓存池之中
			# 所以在这里就不关闭mysql链接了
			# \mysqli_close($link);

			return $schema;
		}		
		
		/**
		 * 使用这个函数来打开和mysql数据库的链接
		 * 
		 * ### 2018-08-08
		 * 
		 * 对于新安装的centos服务器，php假若没有进行配置的话，可能会出现下面的错误：
		 * 
		 *    PHP Fatal error:  Uncaught Error: Call to undefined function mysqli_connect()
		 * 
		 * 则这是因为没有启用php的mysql拓展，则需要在php.ini之中
		 * 启用mysql相关的拓展，找到下面的两个选项，取消掉注释，然后重启apache服务器即可
		 * 
		 *    extension=php_mysql.dll
		 *    extension=php_mysqli.dll
		 * 
		 * @param boolean $new 指示是否返回新的mysql链接而不是使用链接缓存池之中的旧对象
		 * 
		 * @return mysqli 返回数据库的链接
		*/
		protected function __init_MySql($new = true) {
			if ($new) {
				return $this->openNew();
			} else if (!array_key_exists($this->database, self::$mysqliCache)) {
				self::$mysqliCache[$this->database] = $this->openNew();
			}

			$conn = self::$mysqliCache[$this->database];

			# https://stackoverflow.com/questions/3075116/php-how-to-determine-if-a-database-connection-is-open
			if (is_resource($conn) && get_resource_type($conn) === 'mysql link') {
				# 这个链接是没有被关闭的
				# do nothing
				return $conn;
			} else {
				self::$mysqliCache[$this->database] = $this->openNew();
				return self::$mysqliCache[$this->database];
			}			
		}

		/**
		 * @return mysqli
		*/
		private function openNew() {
			$link = @\mysqli_connect(
				$this->host,   
				$this->user,
				$this->password, 
				$this->database, 
				$this->port
			);
						
			# 2018-07-27
			# 如果连接最新版本的mysql的时候，出现错误
			#
			# Error: Unable to connect to MySQL.
			# Debugging errno: 2054
			# Debugging error: The server requested authentication method unknown to the client
			#
			# 这是因为新版本的mysql采用了新的验证方式，这个时候会需要修改mysql之中的用户验证方式为旧的验证方式
			# 使用下面的sql语句进行修改:
			#
			#    use mysql;
			#    ALTER USER 'native'@'localhost' IDENTIFIED WITH mysql_native_password BY 'new_password';
			#    FLUSH PRIVILEGES;
			#
			# 或者升级php至最新版本

			if (false == $link) {
				$msg = (new \StringBuilder("", "<br />"))
					->AppendLine("Error: Unable to connect to MySQL.")
				    ->AppendLine("Debugging errno: " . mysqli_connect_errno()) 
					->AppendLine("Debugging error: " . mysqli_connect_error())
					->ToString();
					
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

		/**
		 * Returns a string description of the last mysql error
		 * 
		 * @return string The last mysql error
		*/
		public function getLastMySqlError() {
			return \mysqli_error($this->__init_MySql(false));
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