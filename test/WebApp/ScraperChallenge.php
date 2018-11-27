<?php

include "../../package.php";

Imports("php.ScraperChallenge");

$dynamics = new ScraperChallenge();

$math = $dynamics->getMathChallenge();

$php = ScraperChallenge::eval($math["php"]);
$math["php_eval"] = $php;
$js = $math["js"];

echo "<script> $js; console.log(ScraperChallenge) </script>";
echo "<pre><code>" . $math["php"] . "</code></pre>";
echo "<pre><code>" . $math["js"] . "</code></pre>";
echo "<pre><code>PHP eval = " . $math["php_eval"] . "</code></pre>";