<?php

dotnet::Imports("Microsoft.VisualBasic.Strings");

/**
 * MySQL data table model
*/
class Model {
	
	#region "MySql connection info"

    private $database;
    private $user;
    private $password;
    private $host;
	private $port;

	#endregion

	/**
	 * 当前的这个表模型对象实例的最后一条执行的MySql语句
	*/
	private $last_mysql_expression;

    function __construct($database, $user, $password, $host = "localhost", $port = 3306) {
        $this->database = $database;
        $this->user     = $user;
        $this->password = $password;
        $this->host     = $host;
        $this->port     = $port;
    }

	/**
	 * 获取当前的这个实例之中所执行的最后一条MySql语句
	*/
	public function getLastMySql() {
		return $this->last_mysql_expression;
	}

	#region "MySql table schema cache"

	# 在这里通过对表结构信息的缓存操作来减少在进行
	# mysql条件查询表达式构建的过程之中对数据库的
	# 查询请求次数

	/**
	 * tableName => [
	 * 	  schema => table_structure, 
	 *    AI     => "AI key name"
	 * ]
	*/
	private static $describCache = array();

	/**
	 * 从数据库之中获取表结构信息或者从缓存之中获取，如果表结构信息已经被缓存了的话
	 * 
	 * @param Model $driver 当前的class类型的实例，数据库抽象层的底层驱动
	*/
	public static function GetSchema($tableName, $driver) {
		if (!array_key_exists($tableName, self::$describCache)) {
			# 不存在，则进行数据库查询构建
			$schema = $driver->Describe($tableName);
			$schema = self::schemaArray($schema);
			$AI     = self::getAIKey($schema);
			
			self::$describCache[$tableName] = [
				"schema" => $schema, 
				"AI"     => $AI
			];
		}

		return self::$describCache[$tableName];
	}

	/**
	 * Get the field name of the auto increment field.
	*/
	public static function getAIKey($schema) {	

		foreach ($schema as $name => $type) {
			
			$Null    = ($type["Null"]  == "NO");
			$Key     = ($type["Key"]   == "PRI");
			$isAI    = ($type["Extra"] == "auto_increment");			
			$type    =  $type["Type"];		
			$isInt32 = (Strings::InStr("$type", "int"));					
			
			if (($isInt32 == 1) && $isAI) {
				return $name;
			}
		}
		
		return null;
	}
	
	/**
	 * Mysql schema table to php schema dictionary array, 
	 * the key in the dictionary is the field name in 
	 * table.
	*/
	public static function schemaArray($schema) {
        $array = array();

        foreach ($schema as $row) {
            $field = $row["Field"];
            $array[$field] = $row;
        }

        return $array;
    }
	
	/**
	 * 显示mysql表的结构
	 * 
	 * DESCRIBE TableName
	*/
    public function Describe($tableName) {
		$db          = $this->database;
        $SQL         = "DESCRIBE `$db`.`$tableName`;";
        $mysqli_exec = $this->__init_MySql();                        
        $schema      = $this->ExecuteSQL($mysqli_exec, $SQL);

        return $schema;
    }

	#endregion

    /**
	 * 使用这个函数来打开和mysql数据库的链接
	*/
	public function __init_MySql() {	
		$db = mysqli_connect(
			$this->host,   
			$this->user,
            $this->password, 
            $this->database, 
			$this->port
		) or die("Database error: <code>" . mysqli_error() . "</code>"); 
					
		if (False === $db) {
			die("Database connection fail!");
		} else {
			return $db;
		}
	}

	/**
	 * 这个方法主要是用于执行一些无返回值的方法，
	 * 例如INSERT, UPDATE, DELETE等
	*/
    public function exec($SQL, $mysql_exec = NULL) {
		if (!$mysql_exec) {
			 $mysql_exec = $this->__init_MySql();
		}
		
		mysqli_select_db($mysql_exec, $this->database); 
		mysqli_query($mysql_exec, "SET names 'utf8'");

		$out = mysqli_query($mysql_exec, $SQL);                     
		
		# echo var_dump($out);
		# echo var_dump(dotnet::$AppDebug);

		if (dotnet::$AppDebug) {
			dotnet::$debugger->add_mysql_history($SQL);
		}
		if (!$out && dotnet::$AppDebug) {
			dotnet::$debugger->add_last_mysql_error(mysqli_error($mysql_exec));
		}		

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
	public function ExecuteSQL($mysql_exec, $SQL) {
		
		mysqli_select_db($mysql_exec, $this->database); 
		mysqli_query($mysql_exec, "SET names 'utf8'");

		$data = mysqli_query($mysql_exec, $SQL); 		

		if (dotnet::$AppDebug) {
			dotnet::$debugger->add_mysql_history($SQL);
		}
		
		if($data) {
			$out = array();
			
			while($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) { 
				array_push($out, $row);
			}
			
			return $out;
		} else {

			// 这条SQL语句执行出错了，添加错误信息到sql记录之中
			if (dotnet::$AppDebug) {
				dotnet::$debugger->add_last_mysql_error(mysqli_error($mysql_exec));
			}

			return false;
		}
	}
	
	/**
	 * 执行SQL查询然后返回一条数据
	*/
	public function ExecuteScalar($mysql_exec, $SQL) {
		
		mysqli_select_db($mysql_exec, $this->database); 
		mysqli_query($mysql_exec, "SET names 'utf8'");

		$data = mysqli_query($mysql_exec, $SQL); 
		
		if (dotnet::$AppDebug) {
			dotnet::$debugger->add_mysql_history($SQL);
		}
		
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
?>