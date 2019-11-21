<?php

include __DIR__ . "/../../package.php";

dotnet::AutoLoad();

Imports("MVC.MySql.debuggerDriver");

$table = Table::GetDebugger("program", "biodeep_workspace", __DIR__ . "/etc/workspace.php");

$table->where([
    "version" => gt_eq(500),
    "id" => between(900,2000)
])->order_by("app_name desc")
  ->limit(5,55)
  ->select();

console::log($table->getLastMySql());
