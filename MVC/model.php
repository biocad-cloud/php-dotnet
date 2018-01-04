<?php

class Table {

    private $tableName;
    private $driver;
    private $schema;
    private $condition;

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
        $mysqli_exec = $this->driver->__init_MySql();       

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

    }

    // update table
    public function save($data) {

    }

    // delete from
    public function delete($data) {

    }
}
?>