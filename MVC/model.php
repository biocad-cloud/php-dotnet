<?php

Imports("Microsoft.VisualBasic.Strings");
Imports("MVC.MySql.sqlDriver");
Imports("MVC.MySql.schemaDriver");
Imports("MVC.MySql.driver");
Imports("MVC.MySql.join");
Imports("System.Linq.Enumerable");

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
	*/
	private $driver;

	/**
	 * 当前的这个数据表的结构信息
	*/
	private $schema;
	
	/**
	 * 对MySql查询表达式的一些额外的配置信息数组
	 * 例如 where limit order distinct 等
	 * 
	 * 进行链式调用的基础
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

			list($tableName, $condition) = Utils::Tuple($condition);

			$this->tableName = $tableName;
			$this->driver    = $config;
			$this->schema    = new SchemaDriver(
				$this->tableName, 
				$this->driver
			);

		} else {

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
				throw new Exception($msg);
			}
		} 
		
		$this->condition = $condition;
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
			$this->driver
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
	*/
	public function mysqli() {
		return $this->driver->getMySqlLink();
	}

	/**
	 * 对查询的结果的数量进行限制，当只有参数m的时候，表示查询结果限制为前m条，
	 * 当参数n被赋值的时候，表示偏移m条之后返回n条结果
	 * 
	 * @param integer $m ``LIMIT m``
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

		$condition = array_merge($this->condition, $condition);

		# echo var_dump($condition);

		return new Table($this->driver, [
			$this->schema->tableName => $condition
		]);
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
		$condition = null;
		$key       = "";

		# 如果只有一个字段的时候
		if (!is_array($keys)) {
			$key = MySqlScript::KeyExpression($keys);
		} else {
			# 如果是一个字段列表的时候
			$contracts = [];
			
			foreach ($keys as $exp) {
				array_push($contracts, MySqlScript::KeyExpression($exp));
			}
			$key = join(", ", $contracts);			
		}

		if ($desc) {
			$condition["order_by"] = [$key => "DESC"];
		} else {
			$condition["order_by"] = [$key => "ASC"];
		}

		$condition = array_merge($this->condition, $condition);

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
			return MySqlScript::AsExpression($where["model"]);
		} else {
			return $where["expression"];
		}		
	}
	
	/**
	 * 判断条件查询之中的给定的条件是否是不存在？
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
	 * (这个函数影响SELECT UPDATE DELETE，不会影响INSERT操作)
     *	  
     * @param mixed $assert The assert array of the where condition or an string expression.
	 * 
	 * @return Table Returns a new table object instance for expression chaining.
    */
    public function where($assert) {
		$condition = null;

		if (gettype($assert) === 'string') {
			$condition["where"] = ["expression" => $assert];
		} else {
			$condition["where"] = ["model" => $assert];
		}
		
		# 为了不影响当前的表对象实例的condition数组，在这里不直接进行添加
		# 而是使用array_merge生成新的数组来完成添加操作
		if ($this->condition) {
			# null的时候会出现
			# array_merge(): Argument #2 is not an array
			$condition = array_merge($condition, $this->condition);
		}
		
		$next = new Table($this->driver, [
			$this->schema->tableName => $condition
		]);

        return $next;
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

	private function buildJoin() {
		$exp = [];

		if (array_key_exists("left_join", $this->condition)) {
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
        return $this->driver->ExecuteSql($SQL);
    }

	/**
	 * 获取当前的这个实例之中所执行的最后一条MySql语句
	 * 
	 * @return string
	*/
	public function getLastMySql() {
		return $this->driver->getLastMySql();
	}

	/**
	 * select all
	 * 
	 * @param array $fields A string array.
	 * 
	 * @return array
	*/
    public function select($fields = null) {
		$ref     = $this->schema->ref;
        $assert  = $this->getWhere();        
		$orderBy = $this->getOrderBy();
		$limit   = $this->getLimit();
		$join    = $this->buildJoin();
		$fields  = empty($fields) ? "*" : Strings::Join($fields, ", ");

        if ($assert) {
            $SQL = "SELECT $fields FROM $ref $join WHERE $assert";
        } else {
            $SQL = "SELECT $fields FROM $ref $join";
        }	
		if ($orderBy) {
			$SQL = "$SQL $orderBy";
		}	
		if ($limit) {
			$SQL = "$SQL $limit";
		}

		$SQL = $SQL . ";";

        return $this->driver->Fetch($SQL);
    }
	
	/**
	 * select count(*) from where ``...``;
	 * 
	 * @return integer
	*/
	public function count() {
		$ref    = $this->schema->ref;
        $assert = $this->getWhere();             
		$count  = "COUNT(*)";

        if ($assert) {
            $SQL = "SELECT $count FROM $ref WHERE $assert;";
        } else {
            $SQL = "SELECT $count FROM $ref;";
        }
    		
		$count = $this->driver->ExecuteScalar($SQL);
		$count = $count["COUNT(*)"];

		return $count;
	}

	/**
	 * select but limit 1
	*/
    public function find() {
		$ref     = $this->schema->ref;
		$assert  = $this->getWhere();   
		
		// 排序操作会影响到limit 1的结果
		$orderBy = $this->getOrderBy();

        if ($assert) {
            $SQL = "SELECT * FROM $ref WHERE $assert";
        } else {
            $SQL = "SELECT * FROM $ref";
        }	
		if ($orderBy) {
			$SQL = "$SQL $orderBy";
		}	

		$SQL = "$SQL LIMIT 1;";

		return $this->driver->ExecuteScalar($SQL);
    }

	/**
	 * Select and limit 1 and return the field value, if target 
	 * record is not found, then returns false.
	 * 
	 * @param name The table field name. Case sensitive! 
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
	 * (不受where条件以及limit的影响，但是可以使用order by进行结果的排序操作)
	*/
	public function all() {		
		$orderBy = $this->getOrderBy();
		$SQL     = "SELECT * FROM {$this->schema->ref}";

		if ($orderBy) {
			$SQL = "$SQL $orderBy;";
		}

		return $this->driver->Fetch($SQL);
	}
	
	/**
	 * insert into.
	 *
	 * @param array $data table row data in array type
	*/ 
    public function add($data) {

		$ref    = $this->schema->ref;
		$fields = [];
		$values = [];	
		
		// 自增的编号字段
		$auto_increment = $this->schema->auto_increment;

		// 使用这个for循环的主要的目的是将所传入的参数数组之中的
		// 无关的名称给筛除掉，避免出现查询错误
		foreach ($this->schema->schema as $fieldName => $def) {
			if (array_key_exists($fieldName, $data)) {
				
				$value = $data[$fieldName];
				# 使用转义函数进行特殊字符串的转义操作
				# $value = mysqli_real_escape_string($mysqli_exec, $value);

				array_push($fields, "`$fieldName`");
				array_push($values, "'$value'");
				
			} else if ($auto_increment && Strings::LCase($fieldName) == Strings::LCase($auto_increment) ) {
				# Do Nothing
			} else {

                # 检查一下这个字段是否是需要值的？如果需要，就将默认值填上
                if (Utils::ReadValue($def, "Null", "") == "NO") {
					
                    # 这个字段是需要有值的，则尝试获取默认值
                    $default = $def["Default"];

                    if ($default) {

                        array_push($fields, "`$fieldName`");
                        array_push($values, "'$default'");

                    } else {
						
                        # 这个字段需要有值，但是用户没有提供值，而且也不存在默认值
                        # 则肯定无法将这条记录插入数据库
                        # 需要抛出错误？？

                    }
                }
            }
		}
		
		$fields = join(", ", $fields);
		$values = join(", ", $values);
		
		# INSERT INTO `metacardio`.`xcms_files` (`task_id`) VALUES ('ABC');
		$SQL    = "INSERT INTO $ref ($fields) VALUES ($values);";
		$result = $this->driver->ExecuteSql($SQL);

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
	 * @return boolean
	*/ 
    public function save($data) {
		$ref     = $this->schema->ref;
        $assert  = $this->getWhere();        
		$SQL     = "";
		$updates = [];
		
		# UPDATE `metacardio`.`experimental_batches` SET `workspace`='2018/01/31/02-36-49/2', `note`='22222', `status`='10' WHERE `id`='3';
		
		foreach ($this->schema->schema as $fieldName => $def) {
			# 只更新存在的数据，所以在这里只需要这一个if分支即可
			if (array_key_exists($fieldName, $data)) {
				$value = $data[$fieldName];
				$set   = "`$fieldName` = '$value'";
				
				array_push($updates, $set);
			}
		}
		
		$updates = join(", ", $updates);
		$SQL     = "UPDATE $ref SET $updates";
		
		if (!$assert) {
			
			# 更新所有的数据？？？要不要给出警告信息
			$SQL = $SQL . ";";
			
		} else {
			$SQL = $SQL . " WHERE " . $assert . " LIMIT 1;";
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
?>