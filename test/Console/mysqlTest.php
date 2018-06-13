<?php

include "../../package.php";

dotnet::AutoLoad("./etc/config.php");

# 使用默认的数据库链接参数配置信息
$world = new Table("city");

# 当需要链接另外的数据库的时候，就需要使用多数据库配置
# 例如现在服务器上有两个数据库，分别名字为 world 和 world-backup
# 需要查询world数据库，可以使用 ["world" => "city"]
# 需要查询world-backup数据库，可以使用 ["world-backup" => "city"]
$world = new Table(["world" => "city"]);

use MVC\MySql\MySqlDebugger as Driver;

$world = new Table(new Driver("world", "root", "root"), ["city" => []]);

# 基本的查询操作

# 查询出人口数量大于186800的一个城市
$result = $world->where(["Population" => gt(186800)])->find();

echo var_dump($result);

# 查询出名字之中含有字母U，并且人口数量小于186800的10个城市
# 并且按照人口数量降序排序
$result = $world->where([
    "Name"       => like("%U%"), 
    "Population" => lt(186800)
])->limit(10)
  ->order_by("Population", true)
  ->select();

echo var_dump($result);

# 聚合函数，例如查询出最大的人口数量
$result = $world->ExecuteScalar("max('Population')");

echo var_dump($result);

?>