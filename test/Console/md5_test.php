<?php

include __DIR__ . "/../../package.php";

dotnet::AutoLoad();

imports("System.Security.Cryptography.HashAlgorithm");

console::log(HashAlgorithm::file_md5("D:\Database\SID-Map.gz"));