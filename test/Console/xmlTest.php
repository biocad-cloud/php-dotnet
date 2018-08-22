<?php

$xml = "<div style='color: red; font-size:2em;'>
    <p>
    abcde
    </p>
    <br />
    <span id='1' style='color: 1green'>1.yes!</span>
    <span id='2' style='color: 2green'>2.yes!</span>
    <span id='3' style='color: 3green'>3.yes!</span>
    <span id='4' style='color: 4green'>4.yes!</span>
    <hr />
</div>";

include "../../package.php";

Imports("php.Xml");

$xml = (new XmlParser($xml, "contents"))->data;

echo var_dump($xml);

?>