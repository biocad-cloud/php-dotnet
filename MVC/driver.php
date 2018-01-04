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

        $this->debug    = true;
    }

    /*
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

    public function Describe($tableName) {
        $SQL = "DESCRIBE `$tableName`;";
        $mysqli_exec = $this->__init_MySql();                        
        $schema = $this->ExecuteSQL($mysqli_exec, $SQL);

        return $schema;
    }

    public function exec($SQL) {
        $mysqli_exec = $this->__init_MySql();                        
        $out = $this->ExecuteSQL($mysqli_exec, $SQL);
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
			dotnet::printCode($SQL);
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
			dotnet::printCode($SQL);
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