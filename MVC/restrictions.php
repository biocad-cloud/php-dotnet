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
     * @param string $user 当前用户的唯一标识符
     * 
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

    public function day() {
        return Utils::ReadValue($this->rates, "day", -1);
    }

    public function minute() {
        return Utils::ReadValue($this->rates, "min|minute", -1);
    }
}
