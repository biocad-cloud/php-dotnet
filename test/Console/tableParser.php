<?php

include __DIR__ . "/../../package.php";

echo var_dump("33");
imports("Microsoft.VisualBasic.Data.csv.IO");

$path = __DIR__ . "/data/table.csv";
$file = new Microsoft\VisualBasic\Data\csv\FileFormat($path);

foreach($file->PopulateAllRows(false, 2048, true) as $metabolite) {
	echo json_encode($metabolite);
	echo "\n";
}

die;


imports("Microsoft.VisualBasic.Data.csv.Extensions");

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