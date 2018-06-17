<?php

namespace MVC\MySql {

	Imports("Microsoft.VisualBasic.Strings");	
	Imports("MVC.MySql.schemaDriver");
	Imports("MVC.MySql.sqlDriver");

    /**
	 * MySQL data table model.
     * (这个模块会将mysql语句用于具体的数据库查询操作)
    */
    class MySqlExecDriver extends sqlDriver implements ISqlDriver {

		function __construct($database, $user, $password, $host = "localhost", $port = 3306) {
			parent::__construct($database, $user, $password, $host, $port);
		}

		/**
		 * 获取当前的这个实例之中所执行的最后一条MySql语句
		*/
		public function getLastMySql() {
			return parent::getLastMySql();
		}

		/**
		 * 使用这个函数来打开和mysql数据库的链接
		*/
		public function getMySqlLink() {
			return parent::__init_MySql();
		}

		/**
		 * 这个方法主要是用于执行一些无返回值的方法，
		 * 例如INSERT, UPDATE, DELETE等
		*/
		public function ExecuteSql($SQL) {
			$mysql_exec = parent::__init_MySql();			
			
			mysqli_select_db($mysql_exec, parent::GetDatabaseName()); 
			mysqli_query($mysql_exec, "SET names 'utf8'");

			$out = mysqli_query($mysql_exec, $SQL);                     
			
			if (APP_DEBUG) {
				\dotnet::$debugger->add_mysql_history($SQL);
			}
			if (!$out && APP_DEBUG) {
				\dotnet::$debugger->add_last_mysql_error(mysqli_error($mysql_exec));
			}		

			$this->last_mysql_expression = $SQL;

			if (\Strings::StartWith($SQL, "INSERT INTO")) {
				# 尝试获取插入语句所产生的新的自增的id编号
				$id = mysqli_insert_id($mysql_exec);

				# 2018-6-13 在这里需要额外的注意一下，如果表之中没有自增的字段
				# 则id变量可能会是false，但是insert可能是成功的，因为$out可能
				# 不是false，如果直接覆盖掉会出现错误，在这里判断一下
				if ($id) {
					$out = $id;
				}

			} else {
				# do nothing
			}

			mysqli_close($mysql_exec);

			return $out;
		}

		/**
		 * 执行一条SQL语句，假若SQL语句是SELECT语句的话，有查询结果的时候
		 * 会返回记录查询结果的数组集合
		 *
		 * 但是对于UPDATE，INSERT和DELETE这类的数据修改语句而言，都是直接
		 * 返回False的，所以执行这类数据修改的操作的时候就不需要获取返回值
		 * 赋值到变量了
		 *
		 * @param mysqli $mysql_exec: 来自于函数__init_MySql()所创建的数据库连接
		 * @param string $SQL
		 * 
		 * @return boolean|array 如果数据库查询出错，会返回逻辑值False，反之会返回相对应的结果值
		 */
		public function Fetch($SQL) {
			$mysql_exec = parent::__init_MySql();	

			mysqli_select_db($mysql_exec, parent::GetDatabaseName()); 
			mysqli_query($mysql_exec, "SET names 'utf8'");

			$data = mysqli_query($mysql_exec, $SQL); 		

			if (APP_DEBUG) {
				\dotnet::$debugger->add_mysql_history($SQL);
			}
			
			$this->last_mysql_expression = $SQL;

			// 输出
			$out = null;

			if($data) {
				$out = [];
				
				while($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) { 
					array_push($out, $row);
				}		
			
			} else {

				// 这条SQL语句执行出错了，添加错误信息到sql记录之中
				if (APP_DEBUG) {
					\dotnet::$debugger->add_last_mysql_error(mysqli_error($mysql_exec));
				}

				$out = false;
			}

			mysqli_close($mysql_exec);

			return $out;
		}
		
		/**
		 * 执行SQL查询然后返回一条数据
		*/
		public function ExecuteScalar($SQL) {			
			$mysql_exec = parent::__init_MySql();

			mysqli_select_db($mysql_exec, parent::GetDatabaseName()); 
			mysqli_query($mysql_exec, "SET names 'utf8'");

			$data = mysqli_query($mysql_exec, $SQL); 
			
			if (APP_DEBUG) {
				\dotnet::$debugger->add_mysql_history($SQL);
			}
			
			$this->last_mysql_expression = $SQL;

			if ($data) {
				
				// 只返回一条记录数据
				while($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) { 
					return $row;
				}
			} else {
				return false;
			}
		}
    }

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
		function __construct($database, $user, $password, $host = "localhost", $port = 3306, $buffer = null) {
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

		public function ExecuteSql($SQL) {
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