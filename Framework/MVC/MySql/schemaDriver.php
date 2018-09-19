<?php

namespace MVC\MySql {

    Imports("Microsoft.VisualBasic.Strings");
    Imports("MVC.MySql.sqlDriver");

    /**
     * 获取数据库的目标数据表的表结构
    */
    class SchemaInfo {

		public $tableName;
		public $databaseName;
		/**
		 * 数据表的表结构
		*/ 
		public $schema;
		/**
		 * 自增字段的列名称
		*/
		public $auto_increment;

        /**
         * 对数据库之中的表对象的完整引用：
         * 
         * `databaseName`.`tableName`
        */
        public $ref;

		/**
		 * @param string $tableName 目标数据模型的数据库表名称
		 * @param MySqlExecDriver $driver 主要是需要通过这个mysql驱动程序对象的链接信息
		 * 从数据库之中得到表的结构信息，在进行调试的时候，如果存在schemaCache数据的话，
		 * 可以将这个参数设置为空值
		 * 
		 * @param string $schemaCache 数据库结构缓存信息的php文件的文件路径，假若使用``describ``描述来
		 * 从数据库服务器之中得到结构信息的话，每一次创建模型都会链接数据库，导致数据库服务器需要处理的请求
		 * 增多，通过本地生成的mysql结构缓存，可以减少这部分的服务器请求量。
		*/
		function __construct($tableName, $driver, $schemaCache) {
            $this->schema         = self::GetSchema($tableName, $driver);
            $this->auto_increment = $this->schema["AI"];  
            $this->schema         = $this->schema["schema"];	
            $this->databaseName   = $driver->GetDatabaseName();
            $this->tableName      = $tableName;
			$this->ref            = "`{$this->databaseName}`.`{$this->tableName}`";
			
			\debugView::LogEvent("Create Model: {$this->ref}");
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
		private static $describCache = [];

		public static function WriteCache($tableName, $schema) {
			self::$describCache[$tableName] = [
				"schema" => $schema,
				"AI"     => self::GetAutoIncrementKey($schema)
			];
		}

		/**
		 * 从数据库之中获取表结构信息或者从缓存之中获取，如果表结构信息已经被缓存了的话
		 * 
		 * @param sqlDriver $driver 当前的class类型的实例，数据库抽象层的底层驱动
		*/
		public static function GetSchema($tableName, $driver) {
			if (!array_key_exists($tableName, self::$describCache)) {
				# 不存在，则进行数据库查询构建
				$schema = $driver->Describe($tableName);
				$schema = self::ArrayOfSchema($schema);
				$AI     = self::GetAutoIncrementKey($schema);
				
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
		public static function GetAutoIncrementKey($schema) {	

			foreach ($schema as $name => $type) {

				$isAI    = ($type["Extra"] == "auto_increment");			
				$type    =  $type["Type"];		
				$isInt32 = (\Strings::InStr("$type", "int"));					
				
				if (($isInt32 == 1) && $isAI) {
					return $name;
				}
			}
			
			return null;
		}
		
		/**
		 * MySql schema table to php schema dictionary array, 
		 * the key in the dictionary is the field name in 
		 * table.
		*/
		public static function ArrayOfSchema($schema) {
			$array = [];

			foreach ($schema as $row) {
				$field = $row["Field"];
				$array[$field] = $row;
			}

			return $array;
		}

		#endregion
	}
}

?>