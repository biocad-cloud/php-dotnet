<?php

include __DIR__ . "/../../package.php";

dotnet::AutoLoad();

$table = Table::GetDebugger("program", "biodeep_workspace", __DIR__ . "/etc/workspace.php");

$table->where(["id" => gt_eq(500)])->order_by("app_name desc")->limit(5,5)->select();

console::log($table->getLastMySql());
