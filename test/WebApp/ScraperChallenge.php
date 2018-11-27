<?php

session_start();

include "../../package.php";

Imports("php.ScraperChallenge");

$dynamics = new ScraperChallenge();

$math = $dynamics->getMathChallenge();

$php = ScraperChallenge::eval($math["php"]);
$math["php_eval"] = $php;
$js = $math["js"];

echo "<script> $js; console.log(ScraperChallenge) </script>";
echo "<br />";
echo "<br />";
echo "<pre><code>" . htmlentities($math["php"]) . "</code></pre>";
echo "<br />";
echo "<br />";
echo "<pre><code>" . $math["js"] . "</code></pre>";
echo "<br />";
echo "<br />";
echo "<pre><code>PHP eval = " . $math["php_eval"] . "</code></pre>";