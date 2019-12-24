<?php

namespace PHP;

imports("System.Text.RegularExpressions.Regex");
imports("Microsoft.VisualBasic.FileIO.FileSystem");
imports("Microsoft.VisualBasic.Strings");
imports("php.URL");

/** 
 * 主要用于路由器的url重写规则的自动设置
 * 
 * 如果希望能够自动使用URL重写规则，除了序列编写``.htaccess``文件以外，还需要在模板之中使用``[...]``进行标记，
 * 例如：
 * 
 * ```
 * {api/login}&name=hahaha&hash=555555
 * ```
 * 
 * 将会在路由器之中转换为
 * 
 * ```
 * api.php?app=login&name=hahaha&hash=555555
 * ```
 * 
 * 如果在``.htaccess``文件之中定义了如下的重写规则
 * 
 * ```
 * RewriteRule ^login?&name=(.+)&hash=(\d+) api.php?app=login&name=$1&hash=$2
 * ```
 * 
 * 则下面的html模板之中的url会被路由器重写为
 * 
 * ```
 * [{api/login}&name=hahaha&hash=555555] => /login?name=hahaha&hash=555555
 * ```
*/
class htaccess {

    /** 
     * 在当前的网站之中是否启用了URL重写，这个属性只有两种值：``On/Off``
     * 
     * @var string
    */
    var $RewriteEngine;
    /** 
     * URL重写规则集合，如果允许重写的话，则这个集合会影响路由器的工作行为
     * 
     * > 请注意：重写规则之间是有先后顺序的，所以对于一些比较重要的规则，应该尽量写在头部
     * 否则如果该规则在尾部的话，会被其他的相似的靠近头部的规则给覆盖掉
     * 
     * @var RewriteRule[]
    */
    var $RewriteRules;

    /** 
     * 在当前的网站配置之中是否允许重写URL？
    */
    public function AllowRewrite() {
        return $this->RewriteEngine == "On";
    }

    /** 
     * 尝试将HTML模板之中所解析出来的url按照.htaccess规则进行重写
     * 
     * @param string|\URL url字符串或者一个URL对象
    */
    public function TryRewrite($url) {
        if (is_string($url)) {
            $url = \URL::mb_parse_url($url, true, true);
        }

        foreach($this->RewriteRules as $rule) {
            if ($rule->MatchRule($url)) {
                return $rule->RouterRewrite($url);
            }
        }

        # 没有匹配上任何规则
        # 则返回false
        return false;
    }

    /**
     * Parse a given ``.htaccess`` file to data model.
     * 
     * 主要是解析``RewriteRule``重写规则
     * 
     * @param string $path The ``.htaccess`` file its file path. 
    */
    public static function LoadFile($path) {
        $RewriteRules = [];
        $htaccess = new htaccess();

        foreach(\FileSystem::IteratesAllLines($path) as $line) {
            # skip the empty and comment line
            if (\Strings::Empty($line = trim($line))) continue;
            if (\Strings::CharAt($line, 0) == "#") continue;

            list($name, $value) = \StringHelpers::GetTagValue($line, " ", $tuple = true);

            switch ($name) {
                case "RewriteEngine":
                    $htaccess->RewriteEngine = $value;
                    break;
                case "RewriteRule":
                    list($in, $out) = \StringHelpers::GetTagValue($value, " ", $tuple = true);
                    array_push($RewriteRules, new RewriteRule($in, $out));
                    break;
                default:
                    # do nothing
            }
        }

        $htaccess->RewriteRules = $RewriteRules;

        return $htaccess;
    }
}

/** 
 * URL重写规则
*/
class RewriteRule {

    var $urlIn;
    var $urlRewrite;
    /** 
     * @var \URL
    */
    var $rewriteModel;

    /** 
     * 需要在这里解析为url路由器规则
    */
    public function __construct($in, $out) {
        $this->urlIn = $in;
        $this->urlRewrite = $out;
        $this->rewriteModel = \URL::mb_parse_url($out, true, true);
    }

    public function __toString() {
        return "{$this->urlIn} => {$this->urlRewrite}";
    }

    /** 
     * 判断所给定的URL是否符合当前的重写规则
     * 
     * 在这里进行匹配比较，只比较文件路径，以及查询参数名称，参数值不进行比较
     * 
     * @param \URL $url 为了不牺牲程序的性能，这个url函数参数必须是已经
     *    经过``\URL::mb_parse_url($url, true, true);``解析的数组结果
     * @return boolean 目前只支持比较php.NET当前框架的url拼写规则
    */
    public function MatchRule($url) {
        $patternTest = $this->rewriteModel->PatternEquals($url, false);

        if (\StringHelpers::IsPattern($this->rewriteModel->app, "[$]\d+")) {
            # 当前URL的app为通配符，则模式一样
            $appNameTest = true;
        } else {
            $appNameTest = ($this->rewriteModel->app === $url->app);
        }

        return $patternTest && $appNameTest;
    }

    /** 
     * 使用这个函数将router格式的url重写为用户的url
     * 
     * @param string|\URL $url 这个是router的url格式，为真实的url，例如router格式
     *          的url``{index/home}&q=12345``将会被转换为真实
     *          的url``index.php?app=home&q=12345``
     * 
     * @return string 在这里输出用户url，即将``{index/home}&q=12345``
     *          重写为用户url``home?q=12345``
    */
    public function RouterRewrite($url) {
        $template     = "/" . trim($this->urlIn, "^$");
        $model        = $this->rewriteModel;
        $placeHolders = \Regex::Matches($template, "\(.+?\)");
        $placeHolders = \Enumerable::ToDictionary(
            $placeHolders, function($r, $i) {
                return '$' . ($i + 1);
            }); 

        if (is_string($url)) {
            $data = \URL::mb_parse_url($url, true);
        } else {
            $data = $url;
        }

        # 从model和data之中取出，构成一个用于填充模板的数组，例如
        # ["$1" => xxxx, "$2" => yyyyy];
        # 按照键名之中的数值大小升序排序了的
        $modelQuery = $model["query"];
        $additional = [];
        $replaceMap = [];

        foreach($data["query"] as $key => $val) {
            if (array_key_exists($key, $modelQuery)) {
                $modelVal = $modelQuery[$key];

                if (\StringHelpers::IsPattern($modelVal, "[$]\d+")) {
                    $replaceMap[$modelVal] = $val;
                } else {
                    $additional[$key] = $val;
                }
            } else {
                $additional[$key] = $val;
            }
        }
        
        $mapKeys = \Enumerable::OrderBy(
            array_keys($replaceMap), function($x) {
                return $x;
            });

        foreach($mapKeys as $order) {
            $value    = $replaceMap[$order];
            $holder   = $placeHolders[$order]; 
            $template = \StringHelpers::str_replace_once($holder, $value, $template);
        }

        if (count($additional) > 0) {
            $template = $template . "&" . \URL::GetUrlQuery($additional);
        }

        return $template;
    }
}