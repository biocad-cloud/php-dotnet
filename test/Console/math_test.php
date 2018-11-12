<?php

include "../../package.php";

Imports("Microsoft.VisualBasic.Math.Quantile");

$x = [5, 100, 200, 2000, 300, 20, 20, 20, 20, 3000, 9999999, 1, 1, 1, 1, 1, 99];

$gkQuantile = new Microsoft\VisualBasic\Math\Quantile\QuantileEstimationGK($x, 0.0001, 5);
$y = $gkQuantile->query([0, 0.25, 0.5, 0.75, 1]);

echo var_dump($y);