<?php

imports("System.Collection.Generic.Queue");

/**
 * 对某一个服务器资源进行用户访问量的限制
*/
class Restrictions {

    /**
     * 访问量的控制指的是在一段时间内用户的对某一资源的访问次数的限制
     * 在访问量控制的注释标签之中，可以定义多个梯度的访问控制，时间的单位
     * 分别为day, hour, min。
     * 
     * @var array
    */
    var $rates;

    /**
     * 当前用户的唯一标识符
     * 
     * @var string
    */
    var $user;
    /**
     * 当前所访问的服务器资源的唯一标记
     * 
     * @var string
    */
    var $resource;

    public function Description() {
        $limits = "";

        foreach($this->rates as $time => $limit) {
            $limits .= "<li>{$limit} requests/{$time}</li>";
        }

        $str = "<h3>Resource: {$this->user} @ {$this->resource}</h3>";
        $str = "<p>your http requests rate has reached our limitation:
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
     * @param controller $controller 用户访问权限控制器，需要从控制器之中读取访问限制的注释数据
     * @param string $user 当前用户的唯一标识符，这个标识符可以是
     *          1. 用户在数据库之中的id编号，
     *          2. 也可以是一个ip地址，
     *          3. 也可以是用户分组标记
     *     可以根据实际需求进行自定义
    */
    public function __construct($user, $controller) {
        $rates = [];

        $this->rates = $controller->getRateLimits();
        $this->rates = explode(",", $this->rates);
       
        foreach($this->rates as $limit) {
            $limit = explode("/", $limit);
            $rates[strtolower($limit[1])] = floatval($limit[0]);
        }

        if (empty($user)) {
            $user = "NA";
        }

        $this->rates    = $rates;
        $this->resource = $controller->ref;
        $this->user     = $user;

        if (APP_DEBUG) {
            console::log("User visit restrict resource: <code>{$this->user} @ {$this->resource}</code>");
            console::dump($this->rates);
        }
    }

    #region "Get resource restriction values"

    /**
     * 对当前的服务器资源的每天的访问次数限制量
    */
    public function day() {
        return Utils::ReadValue($this->rates, "day", -1);
    }

    /**
     * 对当前的服务器资源的每分钟的访问次数限制量
    */
    public function minute() {
        return Utils::ReadValue($this->rates, "min|minute", -1);
    }

    /**
     * 对当前的服务器资源的每小时的访问次数限制量
    */
    public function hour() {
        return Utils::ReadValue($this->rates, "hour", -1);
    }

    #endregion

    const MINUTE = 60;
    const HOUR   = 3600;
    const DAY    = 86400;

    /**
     * 判断当前用户是否已经超过了访问限制次数
     * 
     * 在进行检查的时候，首先会从服务器的会话文件之中读取相应的数据
     * 在这里不可以从用户的会话之中读取数据？，因为当用户退出登录的时候，数据将会消失，
     * 导致限制计数器被重置
     * 但是如果用户session的id是和用户的数据库编号唯一一一对应的时候，
     * 是否就可以不被重置呢？
     * 
     * @return boolean 返回来的逻辑值表示是否已经超过了访问限制次数
     *     true表示已经超过了限制阈值
     *     false表示还没有超过限制阈值，可以进行正常访问
    */
    public function Check() {        
        $visits = $this->getVisits();
        
        $dayLimits = $this->day();
        $minLimits = $this->minute();
        $hourLimit = $this->hour();

        if (($minLimits > 0) && (self::traceback($visits, self::MINUTE) >= $minLimits)) {
            # 60秒以内的访问次数已经超过了限制数量
            # 则拒绝用户的当前访问
            return true;
        }
        if (($hourLimit > 0) && (self::traceback($visits, self::HOUR) >= $hourLimit)) {
            # 一个小时内的访问次数已经超过了限制数量
            # 则拒绝用户的当前访问
            return true;
        }
        if (($dayLimits > 0) && (self::traceback($visits, self::DAY) > $dayLimits)) {
            # 一天内的访问次数已经超过了限制数量
            # 则拒绝用户的当前访问
            return true;
        }

        return false;
    }

    /**
     * 这个函数在获取得到uid指定的访问历史的同时还会向数据源中写入当前的访问
     * 
     * @param string $uid resources visit id for current user
     * 
     * @return array 历史访问的时间点的timestamp
    */
    private function getVisits() {
        $uid    = "{$this->user} @ {$this->resource}";
        $key    = substr($this->user, 1, 3);
        $key2   = substr($this->user, 3, 3);
        $logs   = dotnet::getMyTempDirectory() . "/visits/{$key}/{$key2}/{$this->user}.log";
        $visits = FileSystem::ReadAllText($logs, "{}");
        $visits = json_decode($visits);
        $now    = time();

        if (APP_DEBUG) {
            console::log("Visit Restrictions Logs: $logs");
        }

        // json_decode函数返回来的是一个对象
        // 不可以直接使用数组的方式进行数据读取操作
        $q = new Queue(Utils::ReadValue($visits, $uid, []));
        $q->Push($now);

        # 将大于一天的间隔的时间点都删除掉
        while (($t = $q->Peek()) > 0) {
            if ($now - $t > self::DAY) {
                # 时间点t和当前的时间间隔已经大于一天了
                # 则删掉
                $q->Pop();
            } else {
                # 数据没有了
                break;
            }
        }

        $visits->{$uid} = $q->ToArray();
        FileSystem::WriteAllText($logs, json_encode($visits));

        return $visits->{$uid};
    }

    /**
     * 回溯指定时间间隔长度，并返回元素的计数
     * 
     * @param array $timePoints
     * @param integer $span The time span length
     * 
     * @return integer 时间间隔内的元素的数量
    */
    private static function traceback($timePoints, $span) {
        $now = time();
        $x   = 0;

        for ($i = count($timePoints) - 1; $i >= 0; $i--) {
            if (($now - $timePoints[$i]) <= $span) {
                $x++;
            } else {
                return $x;
            }
        }

        return $x;
    }
}
