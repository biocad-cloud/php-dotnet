<?php

namespace MVC\MySql {

	imports("Microsoft.VisualBasic.Strings");	
	imports("MVC.MySql.schemaDriver");
	imports("MVC.MySql.sqlDriver");

    /**
	 * MySQL data table model.
     * (这个模块会将mysql语句用于具体的数据库查询操作)
    */
    class MySqlExecDriver extends sqlDriver implements ISqlDriver {

		/**
		 * 这个模块是mysql查询的基础驱动程序模块，是和具体的表模型无关的
		 * 
		 * @param string $database
		 * @param string $user
		 * @param string $password
		 * @param string $host
		 * @param integer $port
		*/
		function __construct(
			$database, 
			$user, 
			$password, 
			$host = "localhost", 
			$port = 3306) {
				
			parent::__construct($database, $user, $password, $host, $port);
		}

		/**
		 * 从给定的配置文件中加载当前的这个mysql的驱动程序模块
		 * 
		 * @param string $configName 当这个参数为空的时候，默认使用master的配置
		 * 
		 * @return MySqlExecDriver
		*/
		public static function LoadDriver($configName = null) {
			if (empty($configName) || strlen($configName) == 0) {
				// master
				$config = \DotNetRegistry::$config;
			} else {
				// slave
				$config = \DotNetRegistry::$config[$configName];
			}
			
			if (empty($config) || false == $config) {
				if (empty($configName)) {
					return "No config file was loaded!";
				} else {
					return "Missing mysql driver config profile for `$configName`!";
				}
			}

			$databaseName = $config["DB_NAME"];
			$driver       = new MySqlExecDriver(
				$config["DB_NAME"], 
				$config["DB_USER"],
				$config["DB_PWD"],
				$config["DB_HOST"],
				$config["DB_PORT"]
			);

			return $driver;
		}

		/**
		 * 获取当前的这个实例之中所执行的最后一条MySql语句
		 * 
		 * @return string
		*/
		public function getLastMySql() {
			return parent::getLastMySql();
		}
		
		/**
		 * Returns a string description of the last mysql error
		 * 
		 * @return string The last mysql error
		*/
		public function getLastMySqlError() {
			return parent::getLastMySqlError();
		}

		/**
		 * 使用这个函数来打开和mysql数据库的链接
		 * 
		 * @return mysqli 这个函数打开的是新的mysql数据库连接
		*/
		public function getMySqlLink() {
			return parent::__init_MySql(true);
		}

		/**
		 * 这个方法主要是用于执行一些无返回值的方法，
		 * 例如INSERT, UPDATE, DELETE等
		 * 
		 * > https://www.php.net/manual/en/mysqli.query.php
		 * 
		 * @return boolean|integer ``insert`` 如果存在``auto_increment``类型的主键的话
		 *     会返回新增的id编号，其他的语句返回true或者false
		*/
		public function ExecuteSql($SQL) {
			
			$mysql_exec = parent::__init_MySql(false);			
			
			mysqli_select_db($mysql_exec, parent::GetDatabaseName()); 
			mysqli_query($mysql_exec, "SET names 'utf8'");

			$bench = new \Ubench();	
			$out   = $bench->run(function() use ($mysql_exec, $SQL) {
				return mysqli_query($mysql_exec, $SQL);
			});		
	
			if (APP_DEBUG) {
				\dotnet::$debugger->add_mysql_history($SQL, $bench->getTime(), "writes");
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

			\debugView::LogEvent("MySql query => ExecuteSql");
			# 因为采用了链接缓存池，所以在这里就不再关闭链接了
			# mysqli_close($mysql_exec);
			
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
			$mysql_exec = parent::__init_MySql(false);

			mysqli_select_db($mysql_exec, parent::GetDatabaseName()); 
			mysqli_query($mysql_exec, "SET names 'utf8'");

			$bench = new \Ubench();
			$data  = $bench->run(function() use ($mysql_exec, $SQL) {
				return mysqli_query($mysql_exec, $SQL);
			});			

			if (APP_DEBUG) {
				\dotnet::$debugger->add_mysql_history($SQL, $bench->getTime(), "queries");
			}
			
			$this->last_mysql_expression = $SQL;

			// 输出
			$out = null;
			$resultStatus = "";

			if($data) {
				$out = [];
				
				while($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) { 
					array_push($out, $row);
				}		
			
				$resultStatus = count($out) . " record";
			} else {

				// 这条SQL语句执行出错了，添加错误信息到sql记录之中
				if (APP_DEBUG) {
					\dotnet::$debugger->add_last_mysql_error(mysqli_error($mysql_exec));
				}

				$out = false;
				$resultStatus = "MySql error!";
			}

			# 因为采用了链接缓存池，所以在这里就不再关闭链接了
			# mysqli_close($mysql_exec);
			\debugView::LogEvent("MySql query => Fetch => $resultStatus");

			return $out;
		}
		
		/**
		 * 执行SQL查询然后返回一条数据
		 * 
		 * 如果查询失败会返回逻辑值false
		 * 
		 * @param string $SQL
		 * 
		 * @return array|boolean
		*/
		public function ExecuteScalar($SQL) {
			$mysql_exec = parent::__init_MySql(false);

			mysqli_select_db($mysql_exec, parent::GetDatabaseName()); 
			mysqli_query($mysql_exec, "SET names 'utf8'");

			$bench = new \Ubench();
			$data = $bench->run(function() use ($mysql_exec, $SQL) {
				return mysqli_query($mysql_exec, $SQL);
			});  
			
			if (APP_DEBUG) {
				\dotnet::$debugger->add_mysql_history($SQL, $bench->getTime(), "queries");
				\debugView::LogEvent("MySql query => ExecuteScalar");
			}
			
			$this->last_mysql_expression = $SQL;

			if (\IS_CLI && \APP_DEBUG) {
				\console::dump($data, "sql query raw output:");
			}

			if (!\Utils::isDbNull($data)) {
				if (self::canLoopIterates($data)) {
					// 只返回一条记录数据
					while($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) { 
						return $row;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		private static function canLoopIterates($data) {
			if (\is_array($data) && \count($data) > 0) {
				return TRUE;
			} else if (\is_object($data) && ($data instanceof Countable) && (\count($data) > 0)) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
    }
}