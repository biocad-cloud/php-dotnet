<?php

/**
 * 配置文件
 */
return [

	// 默认的数据库连接参数配置
	//
	// 使用默认的数据库配置：  $query = (new Table("tableName"))->where([....])->select();
	// 
    'DB_TYPE' => 'mysql',
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'world',
    'DB_USER' => 'root',
    'DB_PWD'  => 'root',
    'DB_PORT' => '3306',
    
	#region "多数据库框架配置"

	/*
	
	使用多数据库配置
	$query = (new Table(["DBName" => "tableName"]))->where([....])->select();
	
	$query = (new Table(["project_biodeep" => "xcms_sample_list"]))->where([....])->select();
	*/


	"world" => [
		'DB_HOST' => 'localhost',
		'DB_NAME' => 'world',
		'DB_USER' => 'root',
		'DB_PWD'  => 'root',
		'DB_PORT' => '3306'
	],
	"project_biodeep" => [
		'DB_HOST' => '192.168.1.237',
		'DB_NAME' => 'project_biodeep',
		'DB_USER' => 'root',
		'DB_PWD'  => 'bionovogene',
		'DB_PORT' => '3306'
	],
	#endregion
	
	// 框架配置参数
	"ERR_HANDLER_DISABLE" => "TRUE",
	// "RFC7231"       => "html/http_errors/"
];
