<?php

$xml = "<div style='color: red; font-size:2em;'>
    <p>
    abcde
    </p>
    <br />
    <span style='color: green'>yes!</span>
</div>";

include "../../package.php";

Imports("php.Xml");

$xml = (new XmlParser($xml, "contents"))->data;

echo var_dump($xml);

?>