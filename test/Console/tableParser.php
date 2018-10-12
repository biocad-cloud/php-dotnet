<?php

include __DIR__ . "/../../package.php";

Imports("Microsoft.VisualBasic.Data.csv.Extensions");

use Microsoft\VisualBasic\Data\csv\Extensions as csv;

echo var_dump(csv::Load( __DIR__ . "/data/table.csv"));


echo "\n\n=====================================================\n\n";

echo var_dump(csv::Load(__DIR__ . "/data/table.xls", true));