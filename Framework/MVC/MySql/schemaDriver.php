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
			if ($schemaCache && file_exists($schemaCache)) {
				# 在自动生成的脚本里面有一个自动加载的函数
				include_once $schemaCache;
			}

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

		/**
		 * 这个函数是开放给外部schema导入的接口
		 * 
		 * 如果是多数据库的结构的话，$ref参数应该是`databaseName`.`tableName`格式的，才可以产生唯一标记
		 * 如果是仅使用单个数据库的话，无所谓
		 * 
		 * @param string $ref 格式为`databaseName`.`tableName`，表示一个数据库表对象的唯一标记
		*/
		public static function WriteCache($ref, $schema) {
			self::$describCache[$ref] = [
				"schema" => $schema,
				"AI"     => self::GetAutoIncrementKey($schema)
			];
		}

		/**
		 * 从数据库之中获取表结构信息或者从缓存之中获取，如果表结构信息已经被缓存了的话
		 * 
		 * @param MySqlExecDriver $driver 当前的class类型的实例，数据库抽象层的底层驱动
		*/
		public static function GetSchema($tableName, $driver) {
			# 2018-10-12 在这里必须要使用db.name的引用形式，否则在多个数据库的时候
			# 假若遇到同名称的表则会出现schema错误的问题
			$key = $driver->GetDatabaseName();
			$key = "`$key`.`$tableName`";

			if (!array_key_exists($key, self::$describCache)) {
				# 不存在，则进行数据库查询构建
				$schema = $driver->Describe($tableName);
				$schema = self::ArrayOfSchema($schema);
				$AI     = self::GetAutoIncrementKey($schema);
				
				self::$describCache[$key] = [
					"schema" => $schema, 
					"AI"     => $AI
				];
			}

			return self::$describCache[$key];
		}

		/**
		 * Get the field name of the auto increment field.
		 * 
		 * @return string
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
		 * 
		 * @return array
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

	class Projector {

		/** 
		 * @param array $data 从数据库之中查询出来得到的一行数据
		 * @param object $fillObj 表对象的数据模型
		*/
		public static function FillModel($data, $fillObj) {
			foreach($data as $name => $value) {
				$fillObj->{$name} = $value;
			}

			return $fillObj;
		}

		/** 
		 * @param array A collection of data rows which are query from the database
		 * @param callable A Function for create target object
		*/
		public static function Fills($rows, $objProvider) {
			$list = [];

			foreach($rows as $row) {
				array_push($list, self::FillModel($row, $objProvider()));
			}

			return $list;
		}

		/** 
		 * Convert user data model object to data array
		 * 
		 * @param object data model
		 * @return array Table row in data array view
		*/
		public static function ToArray($obj) {
			$data = [];

			foreach (get_object_vars($obj) as $name) {
				$data[$name] = $obj->{$name};
			}

			return $data;
		}
	}
}