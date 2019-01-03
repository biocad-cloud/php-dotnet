<?php

/**
 * 调试器的后续rest api的信息输出在当前的session之中的存放键名 
*/
define("DEBUG_SESSION", "debugger(Of php.NET)");

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

	function __construct() {
		$this->mysql_history  = array();
		$this->script_loading = array();
		$this->setError       = FALSE;
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

		$sessionVal = $_SESSION[DEBUG_SESSION][$guid];
		$sessionVal[$checkpoint]["SQL"] = $sql;
		$_SESSION[DEBUG_SESSION][$guid] = $sessionVal;
	}

	public static function GetLoadedFiles() {
		return get_included_files();
	}

	/** 
	 * 判断当前的调用是否为调试器api调用，假设api调试器调用仅发生在index.php
	 * 
	 * ```
	 * index.php?api=debugger
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

		if (Utils::ReadValue($calls->query, "api") != "debugger") {
			return false;
		}

		return true;
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
		$guid        = $_GET["guid"];

		// 调试器的数据是保存在当前的session之中的
		$debuggerOut = $_SESSION[DEBUG_SESSION];
		// 然后通过guid读取当前页面的关联的调试信息
		$debuggerOut = $debuggerOut[$guid];
		// 然后根据checkpoint，读取得到对应的调试器结果数据
		$out = [
			"SQL" => self::getCheckpointValue($debuggerOut["SQL"], $checkpoints["SQL"])
		];
	
		// 最后生成数组，以json返回
		echo json_encode($out);
	}

	/**
	 * ```ts 
	 * interface checkPointValue<T> {
     *     lastCheckPoint: number;
     *     data: T[];
     * }
	 * ```
	*/
	private static function getCheckpointValue($data, $checkpoint) {
		$times          = array_keys($data);
		$lastCheckPoint = -999999;
		$logs           = [];

		foreach($times as $t) {
			if ($t > $checkpoint) {
				if ($t > $lastCheckPoint) {
					$lastCheckPoint = $t;
				}

				foreach($data[$t] as $log) {
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