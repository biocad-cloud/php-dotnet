<?php

$xml = "<div style='color: red; font-size:2em;'>
    <p>
    abcde
    </p>
    <br />
    <span id='1' style='color: green'>1.yes!</span>
    <span id='2' style='color: green'>2.yes!</span>
    <span id='3' style='color: green'>3.yes!</span>
    <span id='4' style='color: green'>4.yes!</span>
</div>";

include "../../package.php";

Imports("php.Xml");

$xml = (new XmlParser($xml, "contents"))->data;

echo var_dump($xml);

?>