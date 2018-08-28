<?php

/**
 * 对某一个服务器资源进行用户访问量的限制
*/
class Restrictions {

    /**
     * 访问量的控制指的是在一段时间内用户的对某一资源的访问次数的限制
     * 在访问量控制的注释标签之中，可以定义多个梯度的访问控制，时间的单位
     * 分别为day, hour, min, second。
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

        $this->rates = $rates;
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

    /**
     * 对当前的服务器资源的每秒的访问次数限制量
    */
    public function second() {
        return Utils::ReadValue($this->rates, "sec|second", -1);
    }

    #endregion

    /**
     * 判断当前用户是否已经超过了访问限制次数
     * 
     * @return boolean 返回来的逻辑值表示是否已经超过了访问限制次数
     *     true表示已经超过了限制阈值
     *     false表示还没有超过限制阈值，可以进行正常访问
    */
    public function Check() {

    }
}
