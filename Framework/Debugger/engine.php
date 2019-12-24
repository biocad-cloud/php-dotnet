<?php

imports("php.URL");

/**
 * 调试器的后续rest api的信息输出在当前的session之中的存放键名 
*/
define("DEBUG_SESSION", "PHP_debugger");

class dotnetDebugger {

	public $script_loading;

	/** 
	 * ``[sql, time, type, err, tag]``
	 * 
	 * @var array
	*/
	public $mysql_history;

	// Mysql query in current session has errors?
	private $setError;
	/** 
	 * 是否已经写入了调试器的session信息？
	 * @var boolean
	*/
	private $debugWrite;

	function __construct() {
		$this->mysql_history  = array();
		$this->script_loading = array();
		$this->setError       = FALSE;
		$this->debugWrite     = FALSE;
	}
	
	public function hasMySqlLogs() {
		return count($this->mysql_history) > 0;
	}

	public function hasMySqlErrs() {
		return $this->setError;
	}

	/**
	 * @param string $SQL
	 * @param string $type + ``writes``表示对数据库进行了sql操作，对数据库的数据有影响， 
	 * 					   + ``queries``表示只是对数据库进行了查询，对数据库的数据没有影响
	*/
	public function add_mysql_history($SQL, $elapsed, $type) {
		$this->mysql_history[] = [
			"sql"  => $SQL, 
			"time" => $elapsed,
			"type" => $type,
			"tag"  => Utils::UnixTimeStamp()
		];
	}

	public function add_loaded_script($path, $refer) {
		$info = array(
			"module"    => $path, 
			"size"      => filesize($path), 
			"initiator" => $refer,
			"time"      => Utils::Now()
		);

		array_push($this->script_loading, $info);
	}

	/**
	 * @return array [sql => error]
	*/
	public function last_mysql_error() {
		return $this->mysql_history[count($this->mysql_history) - 1];
	}

	/**
	 * 如果上一条mysql执行出错了，则可以通过这个函数来将mysql的错误记录下来 
	*/
	public function add_last_mysql_error($error) {
		$lasti = count($this->mysql_history) - 1;
		$last  = $this->mysql_history[$lasti];
		$last["err"] = $error;

		$this->setError              = TRUE;
		$this->mysql_history[$lasti] = $last; 
	}

	/** 
	 * 这个是rest api请求所产生的
	*/
	public function WriteDebugSession() {
		$guid = $_COOKIE[DEBUG_SESSION];
		
		if ($this->debugWrite) {
			return;
		} else {
			$this->debugWrite = true;
		}

		# 生成sql的调试会话信息
		$sql = [];
		$checkpoint = Utils::UnixTimeStamp();

		foreach($this->mysql_history as $log) {
			/*
				interface SQLlog {
					time: string;
					SQL: string;
					runtime: string;
				}
			*/
			$sql[] = [
				"time"    => $checkpoint,
				"SQL"     => $log["sql"],
				"runtime" => $log["time"]
			];
		}

		$sessionVal = $_SESSION[DEBUG_SESSION][$guid]["logs"];
		$sessionVal[$checkpoint]["SQL"]         = $sql;
		$_SESSION[DEBUG_SESSION][$guid]["logs"] = $sessionVal;
	}

	public static function GetLoadedFiles() {
		return get_included_files();
	}

	/** 
	 * 判断当前的调用是否为调试器api调用，假设api调试器调用仅发生在index.php
	 * 
	 * ```
	 * /index.php?api=debugger  ' 获取调试器后台数据更新
	 * /index.php?api=sql_query ' 查看mysql查询结果 
	 * ```
	 * 
	 * 并且为POST提交方式进行请求
	*/
	public static function IsDebuggerApiCalls() {
		$calls = URL::mb_parse_url(null, true, true);

		if (!IS_POST) {
			return false;
		}

		if (basename($calls->path) != "index.php") {
			return false;
		}

		$api      = Utils::ReadValue($calls->query, "api");
		$apiNames = ["debugger", "sql_query", "asset"];

		if (!in_array($api, $apiNames)) {
			return false;
		} else {
			return true;
		}
	}

	/** 
	 * Sesion id + unix timestamp
	*/
	public static function getCurrentDebuggerGuid() {
		# 因为一个页面所发出来的rest api请求肯定是在同一个session之中的
		# 所以没有必要添加session_id前缀了
		return Utils::UnixTimeStamp();
	}

	/** 
	 * 1. 因为当前的数据请求是和其他的数据请求分开的
	 *    所以在获取SQL的调试信息的时候不能够直接读取当前对象的数组数据
	 * 2. 获取信息只能够从session来完成
	*/
	public static function handleApiCalls() {	
		$checkpoints = $_POST;
		$guid        = $_POST["guid"];
		$apiPointTo  = $_GET["api"];

		if ($apiPointTo == "debugger") {
			header("HTTP/1.1 200 OK");
			header("Content-Type: application/json");
			// 返回后台调试器数据更新

			// 调试器的数据是保存在当前的session之中的
			$debuggerOut = $_SESSION[DEBUG_SESSION];
			// 然后通过guid读取当前页面的关联的调试信息
			$debuggerOut = $debuggerOut[$guid];
			// 然后根据checkpoint，读取得到对应的调试器结果数据
			$out = [
				"SQL" => self::getCheckpointValue($debuggerOut["logs"], "SQL", $checkpoints["SQL"])
			];

			// 最后生成数组，以json返回
			echo json_encode($out);

		} elseif ($apiPointTo == "sql_query") {
			header("HTTP/1.1 200 OK");
			header("Content-Type: application/json");

			// 查询数据库，然后返回结果
			$sql = trim($_POST["sql"]);
			$configName = Utils::ReadValue($_POST, "configName");
			$mysql = MVC\MySql\MySqlExecDriver::LoadDriver($configName);

			# 只执行查询操作
			# insert/update/delete/replace之类的修改数据库的语句不可以执行
			if (is_string($mysql)) {
				controller::error($mysql);
			} else {
				if (!self::is_dmlCalls($sql)) {
					// select * from `xxx`
					controller::success($mysql->Fetch($sql));
				} else {
					controller::error("<code>DML</code> or <code>DCL</code> is not allowed executed from external calls.");
				}
			}
		} elseif ($apiPointTo == "asset") {
			self::assets($_GET["resource"]);
		}
	}

	/**
	 * 因为使用base64将js或者css镶嵌进入调试器页面中
	 * 可能会导致语法错误的问题出现
	 * 所以在这里不在通过base64镶嵌脚本代码，而是通过
	 * http请求来进行库文件的加载来避免可能的语法错误而导致页面无法正常显示
	*/
	private static function assets($file) {
		echo file_get_contents(__DIR__ . "/template/$file");
	}

	private static function is_dmlCalls($sql) {
		imports("Debugger.SqlFormatter");

		$tokens = SqlFormatter::tokenize($sql);

		foreach($tokens as $token) {
			$type  = $token[SqlFormatter::TOKEN_TYPE];
			$token = $token[SqlFormatter::TOKEN_VALUE];
			$token = strtolower($token);
			
			if ($type == SqlFormatter::TOKEN_TYPE_RESERVED || $type == SqlFormatter::TOKEN_TYPE_RESERVED_TOPLEVEL) {
				if ($token == "delete"  || 
					$token == "update"  || 
					$token == "drop"    || 
					$token == "replace" ||
					$token == "create"  ||
					$token == "alter"   ||
					$token == "insert"  ||
					$token == "grant"   ||
					$token == "revoke") {

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * ```ts 
	 * interface checkPointValue<T> {
     *     lastCheckPoint: number;
     *     data: T[];
     * }
	 * ```
	*/
	private static function getCheckpointValue($data, $key, $checkpoint) {
		$times          = array_keys($data);
		$lastCheckPoint = -999999;
		$logs           = [];
		$checkpoint     = intval($checkpoint);

		foreach($times as $t) {
			$t = intval($t);

			if ($t > $checkpoint) {
				if ($t > $lastCheckPoint) {
					$lastCheckPoint = $t;
				}

				$t              = strval($t);
				$checkpointData = $data[$t][$key];

				foreach($checkpointData as $log) {
					$logs[] = $log;
				}
			}
		}

		return [
			"lastCheckPoint" => $lastCheckPoint,
			"data"           => $logs
		];
	}
}