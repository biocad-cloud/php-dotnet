<?php

Imports("System.Math");

use \System\Math as Math;

/**
 * 反爬虫模块
 * 
 * 这个模块适用于数据请求不太频繁，但是比较重要的数据源的访问保护
 * 
 * 首先，在服务器端，随机生成一个javascript代码，并得到验证计算结果
 * 然后返回浏览器端，浏览器端使用eval进行动态代码的执行
 * 浏览器端计算出结果之后，将结果返回服务器端，服务器端验证结果
 * 验证成功之后再返回所请求的数据
*/
class ScraperChallenge {

    #region "javascript 对象名称转换为php对象名称"

    # 数组的左边是返回给浏览器端的javascript代码部分
    # 右边则是php的代码部分，用于在服务器端生成验证结果

    private static $math_funcs = [
        "Math.abs"    => ["abs"         => 1],
        "Math.acos"   => ["acos"        => 1],
        "Math.acosh"  => ["acosh"       => 1],
        "Math.asin"   => ["asin"        => 1],
        "Math.asinh"  => ["asinh"       => 1],
        "Math.atan"   => ["atan"        => 1],
        "Math.atan2"  => ["atan2"       => 1],
        "Math.atanh"  => ["atanh"       => 1],
        "Math.cbrt"   => ["Math::cbrt"  => 1],
        "Math.ceil"   => ["ceil"        => 1],
        # "Math.clz32"  => null,
        "Math.cos"    => ["cos"         => 1],
        "Math.cosh"   => ["cosh"        => 1],
        "Math.exp"    => ["exp"         => 1],
        "Math.expm1"  => ["expm1"       => 1],
        "Math.floor"  => ["floor"       => 1],
        # "Math.fround" => null,
        "Math.hypot"  => ["hypot"       => 1],
        # "Math.imul"   => null,
        "Math.log"    => ["log"         => 2],
        "Math.log10"  => ["log10"       => 1],
        "Math.log1p"  => ["log1p"       => 1],
        "Math.log2"   => ["Math::log2"  => 1],
        "Math.max"    => ["max"         => 2],
        "Math.min"    => ["min"         => 2],
        "Math.pow"    => ["pow"         => 2],
        # "Math.random" => null, // 因为随机数可能会导致不一样的验证结果，所以在这里不使用随机数
        "Math.round"  => ["round"       => 2],
        "Math.sign"   => ["Math::sign"  => 1],
        "Math.sin"    => ["sin"         => 1],
        "Math.sinh"   => ["sinh"        => 1],
        "Math.sqrt"   => ["sqrt"        => 1],
        "Math.tan"    => ["tan"         => 1],
        "Math.tanh"   => ["tanh"        => 1],
        "Math.trunc"  => ["Math::trunc" => 1]
    ];

    private static $math_const = [
        "Math.E"       => Math::E,
        "Math.LN10"    => Math::LN10,
        "Math.LN2"     => Math::LN2,
        "Math.LOG10E"  => Math::LOG10E,
        "Math.LOG2E"   => Math::LOG2E,
        "Math.PI"      => Math::PI,
        "Math.SQRT1_2" => Math::SQRT1_2,
        "Math.SQRT2"   => Math::SQRT2
    ];

    /** 
     * 下面的操作符都是在php和javascript的含义一样的，两种语言共有的操作符
     * 
     * @var string[]
    */
    private static $math_op = ["+", "-", "*", "/", "**", "%"];

    /** 
     * @var string[]
    */
    private $mathNames;
    /** 
     * @var string[]
    */
    private $constNames; 

    public function __construct() {
        $this->mathNames  = array_keys(self::$math_funcs);
        $this->constNames = array_keys(self::$math_const);

        if (!array_key_exists("ScraperChallenge", $_SESSION)) {
            $_SESSION["ScraperChallenge"] = [];
        }
    }

    #endregion

    public function getRandomMath() {
        $fi   = rand(0, count($this->mathNames));
        $js   = $this->mathNames[$fi];
        $argv = ["js" => [], "php" => []];

        # 获取得到javascript对应的php函数
        # 以及数学函数所需要的参数个数
        list($php, $args) = Utils::Tuple(self::$math_funcs[$js]);

        for ($i = 0; $i < $args; $i++) {
            $a = $this->getRandomNumber();

            array_push($argv["js"],  $a["js"]);
            array_push($argv["php"], $a["php"]);
        }

        $argv = [
            "js"  => implode(", ", $argv["js"]),
            "php" => implode(", ", $argv["php"])
        ];

        # 生成函数表达式
        $js  = "$js("  . $argv["js"]  . ")";
        $php = "$php(" . $argv["php"] . ")";

        return ["js" => $js, "php" => $php];
    }

    public function getRandomNumber() {
        if (self::flipCoin(3)) {
            $ci  = rand(0, count($this->constNames));
            $js  = $this->constNames[$ci];
            $php = self::$math_const[$js];
        } else if(self::flipCoin(6)) {
            $js  = self::getRandom();
            $php = $js;
        } else {
            # 函数表达式也可以是函数的参数
            return $this->getRandomMath();
        }

        return [
            "js"  => $js, 
            "php" => $php
        ];
    }

    /** 
     * @return double
    */
    public static function getRandom() {
        $sign  = self::flipCoin() ? 1 : -1;
        $rnd   = rand(-10000, 10000) * 1.2365;
        $float = $sign * $rnd / 10000 * rand(1, 5);
        
        return $float;
    }

    /** 
     * @param integer $threshold 硬币的正面向上的临界值，这个临界值越小，越容易发生正面向上
     *    的事件，即函数返回``TRUE``
     * @param integer $max 硬币的抛掷的次数
     * 
     * @return boolean
    */
    public static function flipCoin($threshold = 5, $max = 10) {
        $coin = rand(0, $max);

        if ($coin > $threshold) {
            return true;
        } else {
            return false;
        }
    }

    public function getMathChallenge() {
        $tokens = ["js" => [], "php" => []];
        $exp = $this->getRandomMath();
        $n = rand(5, 13);

        array_push($tokens["js"],  $exp["js"]);
        array_push($tokens["php"], $exp["php"]);

        for ($i = 0; $i < $n; $i++) {
            # 操作符
            $op = self::$math_op[rand(0, count(self::$math_op))];

            array_push($tokens["js"],  $op);
            array_push($tokens["php"], $op);

            $exp = $this->getRandomMath();
            array_push($tokens["js"],  $exp["js"]);
            array_push($tokens["php"], $exp["php"]);
        }

        // js 表达式于浏览器端运行
        $js  = "var ScraperChallenge = " . implode(" ", $tokens["js"]) . ";";
        // php 表达式于服务器端后台运行。用作js的运行结果的验证
        $php = "<?php return " . implode(" ", $tokens["php"]) . ";"; 

        return ["js" => $js, "php" => $php];
    }

    public static function eval($php) {
        $filename = "php://memory";
        $fp       = fopen($filename, "w+b");

        fwrite($fp, $php);
        rewind($fp);

        $result = include "php://memory";

        fclose($fp);

        return $result;
    }

    /** 
     * 随机生成javascript代码，并返回结果
    */
    public function getChallenge() {
        $challenge = [];
        $uid       = "T" . strval(time());

        if (self::flipCoin(3)) {
            # 硬币的正面向上为数学函数计算
            $challenge = $this->getMathChallenge();
        } else {
            # 为字符串验证计算

        }
        
        $php = self::eval($challenge["php"]);
        # php结果在运算完成之后，写入session之中
        $_SESSION["ScraperChallenge"][$uid] = $php;
        # js则echo回前端，由浏览器运行，返回结果以证明其为浏览器而非爬虫脚本

        return [
            "uid" => $uid, 
            "js"  => $challenge["js"]
        ];
    }

    /** 
     * 验证浏览器端返回来的结果是否正确
    */
    public function Verify($uid, $result) {
        $val = $_SESSION["ScraperChallenge"][$uid];
        return $result == $val;
    }
}