<?php

namespace PHP;

Imports("System.Text.RegularExpressions.Regex");
Imports("Microsoft.VisualBasic.FileIO.FileSystem");
Imports("Microsoft.VisualBasic.Strings");

/** 
 * 主要用于路由器的url重写规则的自动设置
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
            if (\Strings::CharAt(trim($line), 0) == "#") continue;

            list($name, $value) = \StringHelpers::GetTagValue($line);

            switch ($name) {
                case "RewriteEngine":
                    $htaccess->RewriteEngine = $value;
                case "RewriteRule":
                    list($in, $out) = \StringHelpers::GetTagValue($value);
                    array_push($RewriteRules, new RewriteRule($in, $out));
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
     * 需要在这里解析为url路由器规则
    */
    public function __construct($in, $out) {

    }

    /** 
     * 判断所给定的URL是否符合当前的重写规则
     * 
     * @param string $url
     * @return boolean
    */
    public function MatchRule($url) {
        $s = \Regex::Match($url, $this->urlIn);
        return (!empty($s) && strlen($s) > 0);
    }
}