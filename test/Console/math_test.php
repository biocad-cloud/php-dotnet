<?php

include __DIR__ . "/../../package.php";

imports("Microsoft.VisualBasic.Math.Quantile");

$list = ArrayList::From(1,2,3,4,5,6,7,8,9,0);

echo var_dump( json_encode( $list->ToArray()));

$list->InsertAt(1, 99);

echo var_dump( json_encode( $list->ToArray()));

$list->RemoveAt(10);

echo var_dump( json_encode( $list->ToArray()));

$list->Add(88);
echo var_dump( json_encode( $list->ToArray()));
$list->RemoveLast();

echo var_dump( json_encode( $list->ToArray()));

$x = [5, 100, 200, 2000, 300, 20, 20, 20, 20, 3000, 9999999, 1, 1, 1, 1, 1, 99];

$gkQuantile = new Microsoft\VisualBasic\Math\Quantile\QuantileEstimationGK($x, 0.0001, 5);

$y = $gkQuantile->query([0, 0.25, 0.5, 0.75, 1]);

# > x = c(5, 100, 200, 2000, 300, 20, 20, 20, 20, 3000, 9999999, 1, 1, 1, 1, 1, 99);
# > quantile(x);
#      0%     25%     50%     75%    100%
#       1       1      20     200 9999999

echo var_dump($y);