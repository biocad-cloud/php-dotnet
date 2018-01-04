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

    // 显示mysql表的结构
    // DESCRIBE TableName
    function __construct($database, $user, $password, $host = "localhost", $port = 3306) {
        $this->database = $database;
        $this->user     = $user;
        $this->password = $password;
        $this->host     = $host;
        $this->port     = $port;
    }

    public function Describe($tableName) {

    }
}

class Table {

    private $tableName;

    function __construct($tableName) {
        $this->tableName = $tableName;
    }

    // select all
    public function select() {

    }

    // select but limit 1
    public function find() {

    }

    /*
     * @param $assert: The assert array of the where condition.
     * 
     */
    public function where($assert) {

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