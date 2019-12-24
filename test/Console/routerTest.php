<?php

include __DIR__ . "/../../package.php";

imports("php.htaccess");
imports("php.URL");
imports("MVC.router");

$url = "/dict.php?app=search&q=browse&FORM=BDVSP6&mkt=zh-cn";

console::dump(URL::mb_parse_url($url, true));

$rule = new \PHP\RewriteRule("^dict/(\S+)?q=(.+)&FORM=(.+)", "/dict.php?app=$1&q=$2&FORM=$3");

console::dump($rule);

echo "\n\n\n\n\n\n\n\n\n\n\n";

console::log("====================================================================");


console::dump($url, "The input url is:");
console::dump($rule->RouterRewrite($url), "The output url is:");


$htaccess = \PHP\htaccess::LoadFile(__DIR__ . "/htaccess.txt");

console::dump($htaccess);

echo "\n\n\n\n\n\n\n\n\n\n\n";

console::log("====================================================================");

console::log("URL pattern match test:");

$test1 = URL::mb_parse_url("/dict.php?app=search&q=browse&FORM=BDVSP6&mkt=zh-cn", true, true);
$test2 = URL::mb_parse_url("/dict.php?app=searchTool&q=browse&FORM=BDVSP6&mkt=zh-cn", true, true);

# rule是使用通配符匹配任意app
# 下面的两个匹配肯定被判断为相同
console::log(intval($rule->MatchRule($test1)));
console::log(intval($rule->MatchRule($test2)));

# rule是使用一个固定的常数字符串
# 下面的两个匹配有一个不相同
$rule2 = new \PHP\RewriteRule("^dict/(\S+)?q=(.+)&FORM=(.+)", "/dict.php?app=searchTool&q=$2&FORM=$3");
console::log(intval($rule2->MatchRule($test1)));
console::log(intval($rule2->MatchRule($test2)));