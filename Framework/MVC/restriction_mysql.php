<?php

/**
 * CREATE TABLE `user_rate_limits` (
 *   `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 *   `user_hash` VARCHAR(32) NOT NULL COMMENT '用户唯一标识符的MD5哈希',
 *   `resource` VARCHAR(255) NOT NULL COMMENT '资源标识符',
 *   `visit_time` INT UNSIGNED NOT NULL COMMENT '访问时间戳',
 *   PRIMARY KEY (`id`),
 *   INDEX `idx_user_resource_time` (`user_hash`, `resource`, `visit_time`)
 *  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户访问速率限制日志表';
 */

/**
 * 对某一个服务器资源进行用户访问量的限制
 * 
 * 基于MySQL存储重构版本
*/
class RestrictionMySQL {

    /**
     * 数据库连接配置
     * 请根据实际情况修改以下常量
    */
    private static $DB_HOST = 'localhost';
    private static $DB_USER = 'root';
    private static $DB_PASS = 'password';
    private static $DB_NAME = 'database_name';
    private static $DB_TABLE = 'user_rate_limits';

    /**
     * 访问量的控制指的是在一段时间内用户的对某一资源的访问次数的限制
     * @var array
    */
    private $rates;

    /**
     * 当前用户的唯一标识符(md5 hashed)
     * @var string
    */
    private $user;
    
    /**
     * @var string
    */
    private $source_id;

    /**
     * 当前所访问的服务器资源的唯一标记
     * @var string
    */
    private $resource;

    /**
     * mysqli 连接实例
     * @var mysqli
    */
    private static $db = null;

    /**
     * 获取数据库连接单例
     * @return mysqli
    */
    private function getDB() {
        if (self::$db === null) {
            self::$db = new mysqli(self::$DB_HOST, self::$DB_USER, self::$DB_PASS, self::$DB_NAME);
            if (self::$db->connect_error) {
                // 在生产环境中，建议记录日志而不是直接抛出异常，这里为了调试方便使用die
                RFC7231Error::err500("Access Controller Database Connection Failed: " . self::$db->connect_error);
            }
            self::$db->set_charset("utf8mb4");
        }
        return self::$db;
    }

    public function Description() {
        $limits = "";

        foreach($this->rates as $time => $limit) {
            $limits .= "<li>{$limit} requests/{$time}</li>";
        }

        $str = "<h3>Resource: {$this->user} @ {$this->resource}</h3>";
        $str .= "<p>your http requests rate has reached our limitation:
                    <ul>
                        $limits
                    </ul>
                </p>
                <p>
                this error has been reported to the website administrator.
                </p>";

        return $str;
    }

    /**
     * 从一个控制器实例对象构建出一个访问次数控制器
     * 
     * @param mixed $user 当前用户的唯一标识符
     * @param mixed $controller 用户访问权限控制器
    */
    public function __construct($user, $controller) {
        // 兼容原有逻辑：获取限制配置
        // 假设 $controller 拥有 getRateLimits 方法
        $rates = $controller->getRateLimits();
        
        if (strlen( $rates) == 0) {
            $this->rates = [];
            return;
        } else {
            $this->rates = explode(",", $rates);
        }
        
        $rates = [];

        foreach($this->rates as $limit) {
            $limit = explode("/", $limit);
            if (count($limit) == 2) {
                $rates[strtolower($limit[1])] = floatval($limit[0]);
            }
        }

        if (empty($user)) {
            $user = "NA";
        }

        $this->setup_db();
        $this->rates     = $rates;
        $this->resource  = $controller->ref; // 假设 controller 有 ref 属性
        $this->user      = md5($user);
        $this->source_id = $user;

        // 兼容原有调试逻辑
        if (defined('APP_DEBUG') && APP_DEBUG) {
            // console::log 和 console::dump 需要您的框架支持，这里保留原样
            if (class_exists('console')) {
                console::log("User visit restrict resource: <code>{$this->user} @ {$this->resource}</code>");
                console::dump($this->rates);
            }
        }
    }

    private function setup_db() {
        $dbName = DotNetRegistry::Read("user_audit");

        if (array_key_exists($dbName, DotNetRegistry::$config)) {
			$config = DotNetRegistry::$config[$dbName];

            self::$DB_HOST = $config["DB_HOST"];
            self::$DB_NAME = $config["DB_NAME"];
            self::$DB_USER = $config["DB_USER"];
            self::$DB_PASS = $config["DB_PWD"];

		} else {
			# 无效的配置参数信息
			$msg = "Invalid database name config or database config '$dbName' is not exists!";
			dotnet::ThrowException($msg);
		}
    }

    #region "Get resource restriction values"

    public function day() {
        return isset($this->rates['day']) ? $this->rates['day'] : -1;
    }

    public function minute() {
        if (isset($this->rates['min'])) return $this->rates['min'];
        if (isset($this->rates['minute'])) return $this->rates['minute'];
        return -1;
    }

    public function hour() {
        return isset($this->rates['hour']) ? $this->rates['hour'] : -1;
    }

    #endregion

    const MINUTE = 60;
    const HOUR   = 3600;
    const DAY    = 86400;

    /**
     * 判断当前用户是否已经超过了访问限制次数
     * 
     * @return boolean true表示已经超过了限制阈值，false表示正常
    */
    public function Check() {
        if (count($this->rates) == 0) {
            // 当前的控制器上没有设定相关的限制，直接跳过检查
            return false;
        }

        $now = time();
        $db = $this->getDB();

        // 1. 清理当前用户当前资源的过期数据（超过1天的）
        // 这一步替代了原逻辑中文件读取时的队列清理过程
        $expireTime = $now - self::DAY;
        $stmt = $db->prepare("DELETE FROM " . self::$DB_TABLE . " WHERE user_hash = ? AND resource = ? AND visit_time < ?");
        $stmt->bind_param("ssi", $this->user, $this->resource, $expireTime);
        $stmt->execute();
        $stmt->close();

        // 2. 记录当前访问
        $stmt = $db->prepare("INSERT INTO " . self::$DB_TABLE . " (user_hash, resource, visit_time) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $this->user, $this->resource, $now);
        $stmt->execute();
        $stmt->close();

        // 3. 统计各时间窗口内的访问量
        // 使用一个查询获取所有需要的计数值，效率更高
        // 统计逻辑：计算大于 (now - interval) 的记录数
        $minInterval = $now - self::MINUTE;
        $hourInterval = $now - self::HOUR;
        $dayInterval = $now - self::DAY; // 实际上刚才DELETE后剩下的就是1天内的

        $sql = "SELECT 
                    SUM(CASE WHEN visit_time >= ? THEN 1 ELSE 0 END) as min_count,
                    SUM(CASE WHEN visit_time >= ? THEN 1 ELSE 0 END) as hour_count,
                    COUNT(*) as day_count
                FROM " . self::$DB_TABLE . " 
                WHERE user_hash = ? AND resource = ? AND visit_time >= ?";
        
        $stmt = $db->prepare($sql);
        // 绑定参数：分钟阈值，小时阈值，用户ID，资源ID，天阈值
        $stmt->bind_param("iissi", $minInterval, $hourInterval, $this->user, $this->resource, $dayInterval);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        $minCount = intval($row['min_count']);
        $hourCount = intval($row['hour_count']);
        $dayCount = intval($row['day_count']);

        // 4. 检查限制
        $minLimits = $this->minute();
        $hourLimit = $this->hour();
        $dayLimits = $this->day();

        // 注意：原代码逻辑中 minute 和 hour 使用 >= 判断，day 使用 > 判断
        // 这里严格保持原逻辑
        if (($minLimits > 0) && ($minCount >= $minLimits)) {
            return true;
        }
        if (($hourLimit > 0) && ($hourCount >= $hourLimit)) {
            return true;
        }
        if (($dayLimits > 0) && ($dayCount > $dayLimits)) {
            return true;
        }

        return false;
    }
}
