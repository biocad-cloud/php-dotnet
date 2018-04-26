<?php

dotnet::Imports("Microsoft.VisualBasic.Strings");

/**
 * MySQL data table model
*/
class Model {
    
    private $database;
    private $user;
    private $password;
    private $host;
    private $port;

    function __construct($database, $user, $password, $host = "localhost", $port = 3306) {
        $this->database = $database;
        $this->user     = $user;
        $this->password = $password;
        $this->host     = $host;
        $this->port     = $port;
    }

	/**
	 * Get the field name of the auto increment field.
	*/
	public static function getAIKey($model) {
		$schema = $model->getSchema();
		
		foreach ($schema as $name => $type) {
			
			$Null    = ($type["Null"] == "NO");
			$Key     = ($type["Key"] == "PRI");
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
	 * 使用这个函数来打开和mysql数据库的链接
	*/
	public function __init_MySql() {	
		$db = mysqli_connect(
			$this->host,   
			$this->user,
            $this->password, 
            $this->database, 
            $this->port) or die("Database error: ". mysqli_error()); 
					
		if (False == $db) {
			die("Database connection fail!");
		} else {
			return $db;
		}
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
	 * @param mysqli mysql_exec: 来自于函数__init_MySql()所创建的数据库连接
	 * @param string SQL
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