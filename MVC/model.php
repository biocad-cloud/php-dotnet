<?php

class Table {

    private $tableName;
    private $driver;
    private $schema;
    private $condition;
	private $AI;
	
    function __construct($tableName, $condition = null) {
        $this->tableName = $tableName;
        $this->driver    = dotnet::$config;
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
		$this->AI        = Model::getAIKey($this);
    }

    private function schemaArray() {
        $array = array();

        foreach ($this->schema as $row) {
            $field = $row["Field"];
            $array[$field] = $row;
        }

        return $array;
    }

	public function getSchema() {
		return $this->schema;
	}
	
    public function exec($SQL) {
        return $this->driver->exec($SQL);
    }

    // select all
    public function select() {
        $table  = $this->tableName;
        $assert = $this->getWhere();
        $mysqli_exec = $this->driver->__init_MySql();       

        if ($assert) {
            $SQL = "SELECT * FROM `$table` WHERE $assert;";
        } else {
            $SQL = "SELECT * FROM `$table`;";
        }
        
        return $this->driver->ExecuteSQL($mysqli_exec, $SQL);
    }

    private function getWhere() {		
        if (!$this->condition || count($this->condition) == 0) {
            return null;
        } else {
			// echo "create expression for ";
			// print_r($this->condition);
		}
		
        $assert = array();
        $schema = $this->schema;		
		
        foreach ($this->condition as $field => $value) {
			// print_r($field);
			
            if (array_key_exists($field, $schema)) {
                array_push($assert, "`$field` = '$value'");
            }
        }

        if (count($assert) == 0) {
            if (dotnet::$debug) {
                echo("Where condition requested! But no assert expression can be build: \n");
				echo "Here is the condition that you give me:\n";
				print_r($this->condition);
				echo "This is the table structure of target mysql table:\n";
				print_r($this->schema);
            }
        } else {
            // echo "view result";
            // print_r($assert);
        }

        $assert = join(" AND ", $assert);

        return $assert;
    }

    // select but limit 1
    public function find() {
        $table  = $this->tableName;
        $assert = $this->getWhere();
        $mysqli_exec = $this->driver->__init_MySql();       

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

		$mysqli_exec = $this->driver->__init_MySql(); 
		$table       = $this->tableName;
		$fields      = array();
		$values      = array();
		$uid         = null;
					
		# 检查自增字段
		if ($this->AI) {
			$key = $this->AI;
			
			if (!$data[$key]) {
				# 自增字段还没有值，则将表中目前最大的值+1
				$SQL = "SELECT max(`$key`) as `uid` FROM `$table`;";
				$uid  = $this->driver->ExecuteScalar($mysqli_exec, $SQL);
				
				if (!$uid) {
					$uid = 1;
				} else {
					$uid = $uid["uid"] + 1;
				}
								
				$data[$key] = $uid;
			} else {
				$uid = $data[$key];
			}
			
			# print("$key => $uid");
		}
		
		foreach ($this->schema as $fieldName => $def) {
			if (array_key_exists($fieldName, $data)) {
				
				$value = $data[$fieldName];
				
				array_push($fields, "`$fieldName`");
				array_push($values, "'$value'");
				
			} else {

                # 检查一下这个字段是否是需要值的？如果需要，就将默认值填上
                if ($def["Null"] == "NO") {
					
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
		$SQL = "INSERT INTO `{$table}` ($fields) VALUES ($values);";	
		
        if (!mysqli_query($mysqli_exec, $SQL)) {

            // 可能有错误，给出错误信息
            return false;
			
        } else {
			
            if (!$uid) {
				# 这个表之中没有自增字段，则返回true
				return true;
			} else {
				# 在这个表之中存在自增字段，则返回这个uid
				# 方便进行后续的操作
				return $uid;
			}           
        }	
    }

    // update table
    public function save($data) {

    }

    // delete from
    public function delete($data) {

    }
}
?>