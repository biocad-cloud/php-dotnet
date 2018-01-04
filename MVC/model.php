<?php

/*
 * MySQL data table model
 */
class Model {
    
    private $database;
    private $user;
    private $password;
    private $host;
    private $port;

    private $debug;

    // 显示mysql表的结构
    // DESCRIBE TableName
    function __construct($database, $user, $password, $host = "localhost", $port = 3306) {
        $this->database = $database;
        $this->user     = $user;
        $this->password = $password;
        $this->host     = $host;
        $this->port     = $port;

        $this->debug    = false;
    }

    /*
	 * 使用这个函数来打开和mysql数据库的链接
	 */
	private function __init_MySql() {	
		$db = mysqli_connect(
			$this->host.":".$this->port,   // 格式要求： ipaddress:port
			$this->user,
			$this->password) or die("Database error: ". mysqli_error()); 
					
		if (False == $db) {
			die("Database connection fail!");
		} else {
			return $db;
		}
	}

    public function Describe($tableName) {
        $SQL = "DESCRIBE `$tableName`;";
        $mysqli_exec = $mysqli->__init_MySql();                        
        $schema = $mysqli->ExecuteSQL($mysqli_exec, $SQL);

        return $schema;
    }

    public function exec($SQL) {
        $mysqli_exec = $mysqli->__init_MySql();                        
        $out = $mysqli->ExecuteSQL($mysqli_exec, $SQL);
        return $out;
    }

    /*
	 * 执行一条SQL语句，假若SQL语句是SELECT语句的话，有查询结果的时候
	 * 会返回记录查询结果的数组集合
	 *
	 * 但是对于UPDATE，INSERT和DELETE这类的数据修改语句而言，都是直接
	 * 返回False的，所以执行这类数据修改的操作的时候就不需要获取返回值
	 * 赋值到变量了
	 */
	public function ExecuteSQL($mysql_exec, $SQL) {
		
		mysqli_select_db($mysql_exec, $this->database); 
		mysqli_query($mysql_exec, "SET names 'utf8'");

		$data = mysqli_query($mysql_exec, $SQL); 

		if ($this->debug) {
			// $this->printCode($SQL);
		}
		
		if($data) {
			$out = array();
			
			while($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) { 
				array_push($out, $row);
			}
			
			return $out;
		} else {
			return false;
		}
	}
	
	/**
	 * 执行SQL查询然后返回一条数据
	 *
	 */
	public function ExecuteScalar($mysql_exec, $SQL) {
		
		mysqli_select_db($mysql_exec, $this->database); 
		mysqli_query($mysql_exec, "SET names 'utf8'");

		$data = mysqli_query($mysql_exec, $SQL); 

		if ($this->debug) {
			// $this->printCode($SQL);
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

class Table {

    private $tableName;
    private $driver;
    private $schema;
    private $condition;

    function __construct($tableName, $condition = null) {
        $this->tableName = $tableName;
        $this->driver    = dotnet::config;
        $this->driver    = new Model(
            $this->driver["DB_NAME"], 
            $this->driver["DB_USER"],
            $this->driver["DB_PWD"],
            $this->driver["DB_HOST"],
            $this->driver["DB_PORT"]
        );

        # 获取数据库的目标数据表的表结构
        $this->schema    = $this->driver->Describe($tableName);
        $this->schema    = $this->schemaArray();
        $this->condition = $condition;        
    }

    private function schemaArray() {
        $array = array();

        foreach ($this->schema as $row) {
            $field = $row["Field"];
            $array[$field] = $row;
        }

        return $array;
    }

    public function exec($SQL) {
        return $this->driver->exec($SQL);
    }

    // select all
    public function select() {
        $table  = $this->tableName;
        $assert = $this->getWhere();
        $mysqli_exec = $mysqli->__init_MySql();       

        if ($assert) {
            $SQL = "SELECT * FROM `$table` WHERE $assert;";
        } else {
            $SQL = "SELECT * FROM `$table`;";
        }
        
        return $this->driver->ExecuteScalar($mysqli_exec, $SQL);
    }

    private function getWhere() {
        if (!$this->condition || count($this->condition) == 0) {
            return null;
        }
        
        $assert = array();
        $schema = $this->schema;

        foreach ($this->condition as $field => $value) {
            if (array_key_exists($field, $schema)) {
                $assert = array_push($assert, "`$field` = '$value'");
            }
        }

        $assert = join(" ", $assert);

        return $assert;
    }

    // select but limit 1
    public function find() {
        $table  = $this->tableName;
        $assert = $this->getWhere();
        $mysqli_exec = $mysqli->__init_MySql();       

        if ($assert) {
            $SQL = "SELECT * FROM `$table` WHERE $assert LIMIT 1;";
        } else {
            $SQL = "SELECT * FROM `$table` LIMIT 1;";
        }
        
        return $this->driver->ExecuteScalar($mysqli_exec, $SQL);
    }

    /*
     * @param $assert: The assert array of the where condition.
     * 
     */
    public function where($assert) {
        $next = new Table($this->tableName, $assert);
        return $next;
    }

    // insert into
    public function add($data) {

    }

    // update table
    public function save($data) {

    }

    // delete from
    public function delete($data) {

    }
}
?>