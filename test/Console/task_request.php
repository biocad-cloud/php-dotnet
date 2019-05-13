<?php

include __DIR__ . "../../../package.php";

dotnet::AutoLoad();

echo var_dump(file_get_contents("http://127.0.0.1:85/?action=shutdown"));