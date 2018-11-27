<?php

session_start();

include "../../package.php";

Imports("php.ScraperChallenge");

$dynamics = new ScraperChallenge();


echo "<p>";
echo \System\Math::cbrt(1.442695040889) % tan(-4.47303875) * cosh(2.718281828459) / min(max(2.718281828459, log(asin(hypot(0.43429448190325)), \System\Math::sign(1.0537453))), floor(1.4142135623731)) % cos(3.1415926535898) - atan(3.1415926535898) / cos(-1.560463);
echo "</p>";

echo "<p>";
# echo round(2.718281828459, 2.718281828459) % expm1(0.70710678118655) / sinh(1.4142135623731) % \System\Math::log2(1.442695040889) + exp(2.302585092994) % abs(0.69314718055995) - atanh(0.70710678118655) * asinh(0.70710678118655) ** sinh(2.718281828459);
echo "</p>";

$math = $dynamics->getMathChallenge();

$php = ScraperChallenge::eval($math["php"]);
$math["php_eval"] = $php;
$js = $math["js"];

echo "<script> $js; console.log(ScraperChallenge); </script>";
echo "<br />";
echo "<br />";
echo "<p>" . htmlentities($math["php"]) . "</p>";
echo "<br />";
echo "<br />";
echo "<p>" . htmlentities($math["js"]) . "</p>";
echo "<br />";
echo "<br />";
echo "<pre><code>PHP eval = " . $math["php_eval"] . "</code></pre>";