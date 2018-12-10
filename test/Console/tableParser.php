<?php

include __DIR__ . "/../../package.php";

Imports("Microsoft.VisualBasic.Data.csv.Extensions");

use Microsoft\VisualBasic\Data\csv\Extensions as csv;

$data = csv::Load( __DIR__ . "/data/table.csv");

foreach($data as $line) {
    echo json_encode($line) . "\n\n";
}

echo "\n\n=====================================================\n\n";

$data = csv::Load(__DIR__ . "/data/table.xls", true);

foreach($data as $line) {
    echo json_encode($line) . "\n\n";
}

echo "\n\n=====================================================\n\n";

$data = csv::LoadTable(__DIR__ . "/data/table.xls");

foreach($data as $line) {
    echo json_encode($line) . "\n\n";
}