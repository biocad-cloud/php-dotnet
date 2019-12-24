<?php

include "../../package.php";

dotnet::AutoLoad();

imports("MVC.View.foreach");
imports("MVC.View.inline");
imports("php.Utils");

$vars["title"] = "测试";

$vars["list"] = [

    ["name" => "1", "value" => 99],
    ["name" => "2341", "value" => 201],
    ["name" => "asd1f", "value" => 990],
    ["name" => "asda1", "value" => 499],
    ["name" => "fggfd1", "value" => 599],
    ["name" => "dfgdf1", "value" => 699],
    ["name" => "fhg1", "value" => 399],
    ["name" => "jjjjj1", "value" => 1099]
];

$vars["id"] = -1000;

# 先替换变量
# 然后foreach
# 最后php内联求值

/* $template = file_get_contents("./view_info.html"); 
# $php = MVC\Views\ForEachView::InterpolateTemplate($template, $vars);
# $php = eval(' ?>' . $php . '<?php ');
*/

# echo $php;

echo View::Load("./view_info.html", $vars);

?>