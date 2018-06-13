<?php

namespace MVC\MySql {

    Imports("Microsoft.VisualBasic.Strings");
    Imports("MVC.MySql.sqlDriver");

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

		function __construct($driver, $tableName) {

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
				$isInt32 = (\Strings::InStr("$type", "int"));					
				
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