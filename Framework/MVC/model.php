<?php

imports("Microsoft.VisualBasic.Strings");
imports("MVC.MySql.sqlDriver");
imports("MVC.MySql.schemaDriver");
imports("MVC.MySql.driver");
imports("MVC.MySql.SqlBuilder.expressionParts");
imports("MVC.MySql.SqlBuilder.Statements");
imports("MVC.MySql.SqlBuilder.sqlBuilder");
imports("System.Linq.Enumerable");
imports("Debugger.SqlFormatter");

use MVC\MySql\Expression\WhereAssert as MySqlScript;
use MVC\MySql\Expression\JoinExpression as JoinScript;
use MVC\MySql\MySqlExecDriver as Driver;
use MVC\MySql\SchemaInfo as SchemaDriver;

/**
 * WebApp data model.
 * 
 * 数据表模型，这个模块主要是根据schema字典构建出相应的SQL表达式
 * 然后通过driver模型进行执行
*/
class Table {

	/**
	 * MySql数据库驱动程序
	 * 
	 * @var MVC\MySql\MySqlExecDriver
	*/
	private $driver;

	/**
	 * 当前的这个数据表的结构信息
	 * 
	 * @var SchemaDriver 
	*/
	private $schema;
	
	/**
	 * 对MySql查询表达式的一些额外的配置信息数组
	 * 例如 where limit order distinct 等
	 * 
	 * 进行链式调用的基础
	 * 
	 * @var array
	*/
    private $condition;
	
	#region "Table Model constructor"

	/**
	 * Create an abstract table model.
	 * 
	 * @param string $condition default is nothing, means all, no filter
	 * @param string|array $config Database connection config, it can be: 
	 *                             + (string) tableName, 
	 *                             + (array) config, or 
	 *                             + (array) [dbname => table] when multiple database config exists.
	*/
    function __construct($config, $condition = null) {
		
		# 2018-6-13 在这个构造函数之中对mysql的连接的初始化都是通过
		# __initBaseOnExternalConfig这个函数来完成的
		# 下面的if分支的差异仅在于不同的路径所获取得到的配置数据的方法上的差异
		
		if (is_string($config)) {	
			// 如果是字符串，则说明这个是数据表的名称
			// 通过表名称来进行初始化	
			$this->__initBaseOnTableName($config);
		} else if(self::isValidDbConfig($config)) {	
			// 如果是有效的数据库连接参数的配置数组
			// 则不会从配置文件之中读取连接参数信息，而是直接使用
			// 这个数组之中所给定的配置参数信息进行数据库的链接		
			$this->__initBaseOnExternalConfig($config["DB_TABLE"], $config);
		} else if (is_object($config) && (
				   get_class($config) === "MVC\MySql\MySqlExecDriver" || 
		           get_class($config) === "MVC\MySql\MySqlDebugger")) {

			// config对象已经是一个可以直接使用的driver对象了
			// 直接进行赋值使用

			# condition = [tableName => condition]
			# 在这里得到的tuple之中，condition可能是空的
			list($tableName, $condition) = Utils::Tuple($condition);

			$this->tableName = $tableName;
			$this->driver    = $config;
			$this->schema    = new SchemaDriver(
				$this->tableName, 
				$this->driver,
				# 是直接从已经存在的数据库驱动对象构建，说明前面可能已经加载了缓存文件，
				# 在这里使用NULL忽略掉可能的重复加载
				NULL
			);

		} else if (is_array($config)) {
			$keys = array_keys($config);
			
			if (is_integer($keys[0])) {
				dotnet::ThrowException("Invalid slave database endpoint configuration: " . json_encode($config));
			} else {
				$this->slaveEndpointConfigFromTuple($config);
			}

		} else if (is_object($config)) {
			$this->slaveEndpointConfigFromTuple($config);
		} else {
			dotnet::ThrowException("Unsupports data was given at here...");
		}
		
		$this->condition = $condition;
	}
	
	/**
	 * @param array|object $config
	*/
	private function slaveEndpointConfigFromTuple($config) {
		// 如果在配置文件之中配置了多个数据库的链接参数信息
		// 则在这里可以使用下面的格式来指定数据库的连接信息的获取
		// 
		// [dbName => tableName] for multiple database config.
		//
		list($dbName, $tableName) = Utils::Tuple($config);

		if (array_key_exists($dbName, DotNetRegistry::$config)) {
			$this->__initBaseOnExternalConfig(
				$tableName, DotNetRegistry::$config[$dbName]
			);
		} else {
			# 无效的配置参数信息
			$msg = "Invalid database name config or database config '$dbName' is not exists!";
			dotnet::ThrowException($msg);
		}
	}

	/**
	 * 这个函数只适用于命令行终端环境下的数据库查询调试
	 * 
	 * @param string $schemaCache 表结构信息的本地缓存php文件的路径
	 * @param string $tableName 所需要进行调试的目标表的名称
	 * 
	 * @return Table
	*/
	public static function GetDebugger($tableName, $database, $schemaCache) {
		$debugger = new \MVC\MySql\MySqlDebugger($database);
		$tbl      = [$tableName => ""];

		if ($schemaCache && file_exists($schemaCache)) {
			include_once $schemaCache;
		} else {
			throw new Exception("No mysqli schema info was found!");
		}

		return new Table($debugger, $tbl);
	}

	/**
	 * 判断目标配置信息是否是有效的数据库连接参数配置数组？
	 * 
	 * @return boolean
	*/
	private static function isValidDbConfig($config) {
		return array_key_exists("DB_TABLE", $config) && 
			   array_key_exists("DB_NAME",  $config) && 
			   array_key_exists("DB_USER",  $config) && 
			   array_key_exists("DB_PWD",   $config) && 
			   array_key_exists("DB_HOST",  $config) && 
			   array_key_exists("DB_PORT",  $config);
	}

	/**
	 * 不通过内部的配置数据而是通过外部传递过来的新的配置数组
	 * 来进行初始化
	*/
	private function __initBaseOnExternalConfig($tableName, $config) {
		# mysql数据库的表结构缓存信息，这个配置可能是不存在的
		# 不存在的时候就需要从数据库进行describ了
		$cacheSchema = Utils::ReadValue($config, "DB_SCHEMA");

		$this->tableName    = $tableName;
		$this->driver       = $config;
		$this->databaseName = $this->driver["DB_NAME"];
        $this->driver       = new Driver(
            $this->driver["DB_NAME"], 
            $this->driver["DB_USER"],
            $this->driver["DB_PWD"],
            $this->driver["DB_HOST"],
            $this->driver["DB_PORT"]
        );

		# 获取数据库的目标数据表的表结构
		$this->schema = new SchemaDriver(
			$this->tableName, 
			$this->driver,
			$cacheSchema
		);
	}

	/**
	 * 通过表名称来初始化
	*/
	private function __initBaseOnTableName($tableName) {
		$this->__initBaseOnExternalConfig($tableName, DotNetRegistry::$config);
	}

	#endregion

	/**
	 * @return SchemaDriver
	*/
	public function getSchema() {
		return $this->schema;
	}
	
	/**
	 * 打开一个新的和mysql数据库的链接对象实例
	 * 
	 * @return mysqli Returns a new mysqli connection.
	*/
	public function mysqli() {
		return $this->driver->getMySqlLink();
	}

	/**
	 * mysqli::real_escape_string -- mysqli::escape_string -- mysqli_real_escape_string — 
	 * Escapes special characters in a string for use in an SQL statement, taking into 
	 * account the current charset of the connection
	 * 
	 * This function is used to create a legal SQL string that you can use in an SQL statement. 
	 * The given string is encoded to an escaped SQL string, taking into account the current 
	 * character set of the connection.
	 * 
	 * @param string $value The mysql cell value string text.
	 * @return string
	*/
	public function EscapeString($value) {
		return $this->mysqli()->escape_string($value);
	}

	/**
	 * 对查询的结果的数量进行限制，当只有参数m的时候，表示查询结果限制为前m条，
	 * 当参数n被赋值的时候，表示偏移m条之后返回n条结果
	 * 
	 * @param integer|integer[] $m ``LIMIT m``
	 * @param integer $n ``LIMIT m,n``
	 * 
	 * @return Table
	*/
	public function limit($m, $n = -1) {
		$condition = null;

		if ($n < 0) {
			$condition["limit"] = $m;
		} else {
			$condition["limit"] = [$m, $n];
		}

		$condition = $this->addOption($condition);

		return new Table($this->driver, [
			$this->schema->tableName => $condition
		]);
	}

	/**
	 * 进行分组操作
	 * 
	 * @param string|array $keys 进行分组操作的字段依据，可以是一个字段或者一个字段的集合
	 * 
	 * @return Table 返回表结构模型对象，用于继续构建表达式，进行链的延伸
	*/
	public function group_by($keys) {
		$key       = self::getKeys($keys);
		$condition = ["group_by" => $key];		
		$condition = $this->addOption($condition);
		
		return new Table($this->driver, [
			$this->schema->tableName => $condition
		]);
	}

	/**
	 * order_by 和 group_by的公用函数
	 * 
	 * @return string
	*/
	private static function getKeys($keys) {	
		# 如果只有一个字段的时候
		if (!is_array($keys)) {
			return MySqlScript::KeyExpression($keys);
		} 
		
		# 如果是一个字段列表的时候
		$contracts = [];

		foreach ($keys as $exp) {
			array_push($contracts, MySqlScript::KeyExpression($exp));
		}
		
		return join(", ", $contracts);
	}

	/**
	 * 对返回来的结果按照给定的字段进行排序操作
	 * 
	 * @param string|array $keys 进行排序操作的字段依据，可以是一个字段或者一个字段的集合
	 * @param boolean $desc 升序排序还是降序排序？默认是升序排序，当这个参数为true的时候为降序排序
	 * 
	 * @return Table
	*/
	public function order_by($keys, $desc = false) {
		if (is_string($keys)) {
			$order = Regex::Match($keys, "\s((asc)|(desc))$");

			# asc/desc only allows one field name
			if (!empty($order) && $order != false) {
				$keys = trim(str_replace($order, "", $keys));
				$key  = "`$keys`";

				if (trim($order) == "asc") {
					$desc = false;
				} else {
					$desc = true;
				}
			} else {
				$key = self::getKeys($keys);
			}
		} else {
			$key = self::getKeys($keys);
		}

		if ($desc) {
			$condition = ["order_by" => [$key => "DESC"]];
		} else {
			$condition = ["order_by" => [$key => "ASC"]];
		}

		$condition = $this->addOption($condition);

		return new Table($this->driver, [
			$this->schema->tableName => $condition
		]);
	}

	#region "condition expression"

	/**
	 * @return string where expression
	*/
    private function getWhere() {

		# 如果条件是空的话，就不再继续构建表达式了
		# 这个SQL表达式可能是没有选择条件的
		# 否则在下面会抛出错误的
		if ($this->is_empty("where")) {
            return null;
        }

		$where = $this->condition["where"];
		# expression -> string
		# model      -> array

		if (!array_key_exists("expression", $where)) {
			// model数组还需要进行拼接
			$model   = $where["model"];
			$asserts = $model["assert"];
			$op      = $model["and"] ? "AND" : "OR";

			return MySqlScript::AsExpression($asserts, $op);
		} else {
			// expression表示其直接是一个可以直接使用的表达式
			return $where["expression"];
		}		
	}
	
	/**
	 * 判断条件查询之中的给定的条件是否是不存在？
	 * 
	 * @return boolean
	*/
	private function is_empty($key) {
		return !$this->condition       || 
		  count($this->condition) == 0 || 
	    (!array_key_exists($key, $this->condition));
	}

	/**
	 * 生成``order by``语句部分
	*/
	private function getOrderBy() {
		if ($this->is_empty("order_by")) {
			return null;
		} 
	
		list($key, $type) = Utils::Tuple($this->condition["order_by"]);

		if ($type === "DESC") {
			return "ORDER BY $key DESC";
		} else {
			return "ORDER BY $key";
		}		
	}

	private function getGroupBy() {
		if ($this->is_empty("group_by")) {
			return null;
		}

		$key = $this->condition["group_by"];
		
		return "GROUP BY $key";
	}

	/**
	 * 生成``limit m``或者``limit m,n``语句部分
	*/
	private function getLimit() {
		if ($this->is_empty("limit")) {
			return null;
		}

		$limit = $this->condition["limit"];

		if (is_array($limit)) {
			$offset = $limit[0];
			$n      = $limit[1];
			
			return "LIMIT $offset,$n";
		} else {
			return "LIMIT $limit";
		}
	}
	
	private function throwEmpty() {
		$debug = "";
		$debug = $debug . "Where condition requested! But no assert expression can be build: \n";
		$debug = $debug . "Here is the condition that you give me:\n";
		$debug = $debug . "<pre><code>";
		$debug = $debug . json_encode($this->condition);
		$debug = $debug . "</code></pre>";
		$debug = $debug . "This is the table structure of target mysql table:\n";
		$debug = $debug . "<pre><code>";
		$debug = $debug . json_encode($this->schema);
		$debug = $debug . "</code></pre>";
		
		dotnet::ThrowException($debug);
	}

    /**
     * Create a where condition filter for the next SQL expression.
	 * (这个函数影响``SELECT``, ``UPDATE``, ``DELETE``，不会影响``INSERT``操作)
     *	  
     * @param mixed $assert The assert array of the where condition or an string expression.
	 * @param boolean $and This option is only works when ``assert`` parameter is an 
	 *    test condition array.
	 * 
	 * @return Table Returns a new ``Table`` object instance for expression chaining.
    */
    public function where($assert, $and = true) {
		$condition = null;

		if (gettype($assert) === 'string') {
			$condition["where"] = ["expression" => $assert];
		} else {
			$condition["where"] = [
				"model" => [
					"assert" => $assert,
					"and"    => $and
				]
			];
		}
					
		$opt  = $this->addOption($condition);
		$next = new Table($this->driver, [
			$this->schema->tableName => $opt
		]);

        return $next;
    }

	private function addOption($option) {
		# 为了不影响当前的表对象实例的condition数组，在这里不直接进行添加
		# 而是使用array_merge生成新的数组来完成添加操作
		if ($this->condition) {
			# null的时候会出现
			# array_merge(): Argument #2 is not an array
			$condition = array_merge($option, $this->condition);
		} else {
			$condition = $option;
		}

		return $condition;
	}

	/**
	 * fieldName => list
	 * 
	 * (这个函数影响SELECT UPDATE DELETE，不会影响INSERT操作)
	 * 
	 * @return Table
	*/
	public function in($assert) {
		$fieldName = array_keys($assert)[0];
		$values    = $assert[$fieldName];
		
		return $this->where([$fieldName => in($values)]);
	}

	#endregion

	#region "JOIN"

	/**
	 * LEFT JOIN
	 * 
	 * @param string $tableName Target table name
	 * 
	 * @return Table
	*/
	public function left_join($tableName) {
		if (strtolower($tableName) == strtolower($this->schema->tableName)) {
			throw new Error("Can not join your self!");
		}		

		if (empty($this->condition)) {
			$this->condition = [];
		}

		if (array_key_exists("left_join", $this->condition)) {
			$opts = Utils::ArrayCopy($this->condition["left_join"]);
			
			# join的格式为
			#
			# 表名称 => On数组
			# 
			if (is_string(Enumerable::Last($opts))) {
				throw new Error("Can not append join option after a unfinished expression!");
			}
		} else {
			$opts = [];
		}

		# 2018-7-18 在这里将表名称放置在条件值之中
		# 那么在下一个on函数赋值条件的时候就可以直接取
		# last元素，即这个表名称字符串来使用了
		$opts[$tableName] = $tableName;
		$condition = Utils::ArrayCopy($this->condition);
		$condition["left_join"] = $opts;

		$next = new Table($this->driver, [
			$this->schema->tableName => $condition
		]);

        return $next;
	}

	/**
	 * LEFT JOIN ... ON
	 * 
	 * @return Table
	*/
	public function on($equals) {
		if (!array_key_exists("left_join", $this->condition)) {
			throw new Error("Unable to find join condition target table!");
		} else if (!is_array($equals)) {
			throw new Error("Join condition expression must be an array!");
		}

		$opts = Utils::ArrayCopy($this->condition["left_join"]);
		$last = Enumerable::Last($opts);

		if (!is_string($last)) {
			throw new Error("Unable to find join condition target table!");
		}

		$opts[$last] = $equals;
		$condition = Utils::ArrayCopy($this->condition);
		$condition["left_join"] = $opts;

		$next = new Table($this->driver, [
			$this->schema->tableName => $condition
		]);

        return $next;
	}

	/**
	 * @return string
	*/
	private function buildJoin() {
		$exp = [];

		if (!$this->is_empty("left_join")) {
			array_push($exp, JoinScript::AsExpression(
				$this->condition["left_join"], "left_join"
			));
		}

		return Strings::Join($exp, " ");
	}

	#endregion

	#region "MySql executation"

	/**
	 * 直接执行一条SQL语句
	 * 
	 * @param string $SQL
	 * 
	 * @return mixed
	*/
    public function exec($SQL) {
		$SQL = trim($SQL);
		$tokens = explode(" ", strtolower($SQL));

		if ($tokens[0] == "select") {
			return $this->driver->Fetch($SQL);
		} else {
			return $this->driver->ExecuteSql($SQL);
		} 
    }

	/**
	 * 获取当前的这个实例之中所执行的最后一条MySql语句
	 * 
	 * @param boolean $code 这个参数用来切换输出的代码字符串的格式，如果这个参数设置为true，
	 *                      则返回带有代码高亮样式的html代码，反之则是纯文本格式的sql代码字符串
	 * 
	 * @return string
	*/
	public function getLastMySql($code = false) {
		$sql = $this->driver->getLastMySql();
		
		if ($code) {
			$sql = SqlFormatter::format($sql);
		}
		return $sql;
	}

	public function getLastMySqlError() {
		return $this->driver->getLastMySqlError();
	}

	private static function getFieldString($fields) {
		if (empty($fields)) {
			return "*";
		} else if (is_string($fields)) {
			return $fields;
		} else if (is_array($fields)) {
			return Strings::Join($fields, ", ");
		} else {
			dotnet::BadRequest("Invalid data type of the selected field names!");
		}
	}

	/**
	 * select all.(函数参数``$fields``是需要选择的字段列表，如果没有传递任何参数的话，
	 * 默认是``*``，即选择全部字段)
	 * 
	 * @param array|string $fields A string array.
	 * @param string $keyBy 如果这个参数不是空的话，则返回来的数组将会使用这个字段的值作为index key.
	 * 
	 * @return array|boolean 当查询出错的时候，这个函数是会返回一个逻辑值false的
	*/
    public function select($fields = null, $keyBy = null) {
		$ref     = $this->schema->ref;
        $assert  = $this->getWhere();
		$orderBy = $this->getOrderBy();
		$groupBy = $this->getGroupBy();
		$limit   = $this->getLimit();
		$join    = $this->buildJoin();
		$fields  = self::getFieldString($fields);

        if ($assert) {
            $SQL = "SELECT $fields FROM $ref $join WHERE $assert";
        } else {
            $SQL = "SELECT $fields FROM $ref $join";
		}	
		if ($groupBy) {
			# 2018-12-20 如果同时出现了groupby 和 order by选项的话
			# group by应该出现在最前面，否则会出现语法错误
			$SQL = "$SQL $groupBy";
		}
		if ($orderBy) {
			$SQL = "$SQL $orderBy";
		}	
		if ($limit) {
			$SQL = "$SQL $limit";
		}

		$data = $this->driver->Fetch($SQL . ";");
		
		if (false === $data) {
			# 20190911
			# 查询出错了
			return false;
		}

		if (!empty($keyBy) && strlen($keyBy) > 0) {
			$out = [];

			foreach($data as $row) {
				$key       = strval($row[$keyBy]);
				$out[$key] = $row;
			}

			return $out;
		} else {
			return $data;
		}
    }
	
	/**
	 * 这个函数通过一个数组返回目标列的所有数据，返回来的列数据一般是一个字符串数组
	 * 
	 * @param string $fieldName 数据表之中的列名称
	 * @return string[] 返回来的列的数据
	*/
	public function project($fieldName) {
		$data  = $this->select([$fieldName]);
		$array = [];

		foreach($data as $row) {
			array_push($array, $row[$fieldName]);
		}

		return $array;
	}

	/**
	 * 计数
	 * 
	 * select count(*) from where ``...``;
	 * 这个方法可能会受到limit或者group by表达式的影响
	 * 
	 * @return integer
	*/
	public function count() {
		$ref     = $this->schema->ref;
		$assert  = $this->getWhere();
		$groupBy = $this->getGroupBy();
		$count   = "COUNT(*)";
		$limit   = $this->getLimit();

        if ($assert) {
            $SQL = "SELECT $count FROM $ref WHERE $assert";
        } else {
            $SQL = "SELECT $count FROM $ref";
        }
			
		if ($groupBy) {
			$SQL = "$SQL $groupBy";
		}
		if ($limit) {
			# 2018-08-17
			# 可能会出现limit的情况是，数据表太大了，如果要求性能的话，不加limit会导致
			# 查询时间过长
			# 当添加了limit的话，会明显加快效率，如果超过了limit，则最多返回limit条数的结果
			# 例如将limit限制为1000，则如果超过了1000，就可以将结果显示为999+
			$SQL = "$SQL $limit";
		}

		$count = $this->driver->ExecuteScalar($SQL);
		$count = $count["COUNT(*)"];

		return $count;
	}

	/**
	 * select but limit 1
	 * 
	 * 如果查询失败会返回逻辑值false
	 * 
	 * @return boolean|array 如果查询成功，则返回行数据，反之返回一个逻辑值false来表示失败
	*/
    public function find($fields = null) {
		$ref     = $this->schema->ref;
		$assert  = $this->getWhere();   
		$join    = $this->buildJoin();		
		// 排序操作会影响到limit 1的结果
		$orderBy = $this->getOrderBy();
		$fields  = self::getFieldString($fields);

        if ($assert) {
            $SQL = "SELECT $fields FROM $ref $join WHERE $assert";
        } else {
            $SQL = "SELECT $fields FROM $ref $join";
        }	
		if ($orderBy) {
			$SQL = "$SQL $orderBy";
		}	

		$SQL = "$SQL LIMIT 1;";

		return $this->driver->ExecuteScalar($SQL);
	}
	
	/**
	 * 获取数据库之中的随机的一条记录，这个数据库必须要存在一个自增的id列作为主键
	 * 
	 * @param string $key 如果该自增的id列的名称不是``id``，则会需要使用这个参数
	 *                    来指定该自增id列的名称
	*/
	public function random($key = "id") {
		$last = $this->order_by($key, true)
					 ->limit(1)
					 ->findfield($key);
		
		if ($last !== false) {
			$rndPick = $this->where([
				$key => gt_eq("~RAND() * $last") # (rand(1, $last))
			])->find(); 

			return $rndPick;
		} else {
			return false;
		}		
	}

	/**
	 * Select and limit 1 and return the field value, if target 
	 * record is not found, then returns false.
	 * 
	 * @param string $name The table field name. Case sensitive! 
	 * 
	 * @return mixed The reuqired field value. 
	*/
	public function findfield($name) {
		$single = $this->find();

		if ($single) {
			return $single[$name];
		} else {
			return false;
		}		 
	}

	/**
	 * 一般用于执行聚合函数查询，例如SUM, AVG, MIN, MAX等
	 * 
	 * @param string $aggregate 聚合函数表达式，例如 ``max(`id`)`` 等
	*/
	public function ExecuteScalar($aggregate) {
		$ref    = $this->schema->ref;
        $assert = $this->getWhere();        

		if (!$aggregate || strlen($aggregate) == 0) {
			throw new Exception("Aggregate expression can not be nothing!");
		}

        if ($assert) {
            $SQL = "SELECT $aggregate FROM $ref WHERE $assert;";
        } else {
            $SQL = "SELECT $aggregate FROM $ref;";
        }
        
		$single = $this->driver->ExecuteScalar($SQL);
		
		if ($single) {
			return $single[$aggregate];
		} else {
			return false;
		}
	}

	/**
	 * select * from `table`;
	 * 
	 * (不受``where``条件以及``limit``的影响，但是可以使用``order by``进行结果的排序操作)
	*/
	public function all() {		
		$orderBy = $this->getOrderBy();
		$groupBy = $this->getGroupBy();
		$join    = $this->buildJoin();
		$SQL     = "SELECT * FROM {$this->schema->ref} $join";

		if ($orderBy) {
			$SQL = "$SQL $orderBy";
		}
		if ($groupBy) {
			$SQL = "$SQL $groupBy";
		}

		return $this->driver->Fetch($SQL);
	}
	
	/**
	 * insert into. (对于具有auto_increment类型的主键的表，这个函数会返回递增之后的主键)
	 *
	 * @param array|object $data table row data in array type
	 * 
	 * @return boolean|integer 返回成功或者失败的逻辑值，如果目标数据表中存在递增id主键的话，
	 *                         则这个函数返回当前所插入的新数据行的``id``值
	*/ 
    public function add($data) {
		$SQL = MVC\MySql\Expression\InsertInto::Sql($data, $this->schema); 
		$result = $this->driver->ExecuteSql($SQL);
		// 自增的编号字段
		$auto_increment = $this->schema->auto_increment;

        if (!$result) {
            // 可能有错误，给出错误信息
            return false;
        } else {
            if (!$auto_increment) {
				# 这个表之中没有自增字段，则返回true
				return true;
			} else {
				# 在这个表之中存在自增字段，则返回这个uid
				# 方便进行后续的操作
				return $result;
			}
        }
    }

    /**
	 * update table
	 * 
	 * @param boolean $limit1 是否是只更新一条记录？默认是只更新一条记录。
	 * @param array $data 需要进行更新的列数据键值对集合
	 * 
	 * @return boolean
	*/ 
    public function save($data, $limit1 = true, $safe = TRUE) {
		$ref     = $this->schema->ref;
        $assert  = $this->getWhere();
		$SQL     = "";
		$updates = [];
		
		# UPDATE `metacardio`.`experimental_batches` SET `workspace`='2018/01/31/02-36-49/2', `note`='22222', `status`='10' WHERE `id`='3';
		
		foreach ($this->schema->schema as $fieldName => $def) {
			# echo var_dump("$fieldName: " . (array_key_exists($fieldName, $data) ? "yes" : "no"));
			
			# 只更新存在的数据，所以在这里只需要这一个if分支即可
			# 更新语句的值可能会存在表达式，表达式的前缀为~符号
			if (array_key_exists($fieldName, $data)) {
				$value = $data[$fieldName];
				$value = MVC\MySql\Expression\WhereAssert::AutoValue($value);
				$set   = "`$fieldName` = $value";
				
				array_push($updates, $set);
			}
		}
		
		$updates = join(", ", $updates);
		$SQL     = "UPDATE $ref SET $updates";
		
		if (!$assert) {
			# 更新所有的数据？？？要不要给出警告信息
			$SQL = $SQL . ";";

			if ($safe) {
				throw new Exception("update entire data table is not allowed when safe mode is turn on!");
			} else {
				console::warn("Execute update entire data table! ($SQL)");
			}

		} else {
			if ($limit1) {
				$SQL = $SQL . " WHERE " . $assert . " LIMIT 1;";
			} else {
				$SQL = $SQL . " WHERE " . $assert . ";";
			}
		}

		# echo $SQL;

		if (!$this->driver->ExecuteSql($SQL)) {
			return false;
		} else {
			return true;
		}
    }

    /**
	 * delete from
	*/ 
    public function delete() {
		$ref    = $this->schema->ref;
        $assert = $this->getWhere();
		
		# DELETE FROM `metacardio`.`experimental_batches` WHERE `id`='4';
		if (!$assert) {
			dotnet::ThrowException("WHERE condition can not be null in DELETE SQL!");
		} else {
			$SQL = "DELETE FROM $ref WHERE $assert;";
		}
				
		if (!$this->driver->ExecuteSql($SQL)) {
			return false;
		} else {
			return true;
		}
	}

	#endregion
}