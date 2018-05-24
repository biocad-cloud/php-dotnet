<?php

include "../../package.php";

dotnet::AutoLoad();

Imports("MVC.View.foreach");
Imports("php.Utils");

$template = <<<EOT
<ul>

    <foreach @balance>
        <li>@balance["time"] &nbsp; @balance["title"]
             <span style='text-align: right; color: @balance["color"]'>@balance["amount"] 元</span>
             
             <foreach @attrs='@balance["attrs"]'>

                <div class="row">@attrs["slot1"]</div>
                <div class="row">@attrs["slot2"]</div>
                <div class="row">@attrs["slot3"]</div>
                <div class="row">@attrs["slot4"]</div>

             </foreach>

	    </li>
 	</foreach>

</ul>

EOT;


echo var_dump(MVC\Views\ForEachView::ParseTemplates($template));
die;


$list = [];

$list[1] = [
    "time"   => Utils::Now(),
    "title"  => "标题1",
    "color"  => "red",
    "amount" => "-500"
];

$list[2] = [
    "time"   => Utils::Now(),
    "title"  => "标题2",
    "color"  => "green",
    "amount" => "+50000"
];

$list[3] = [
    "time"   => Utils::Now(),
    "title"  => "标题3",
    "color"  => "red",
    "amount" => "-5000"
];


echo json_encode($list) . "\n\n";

echo MVC\Views\ForEachView::InterpolateTemplate($template, ["balance" => $list]);

?>