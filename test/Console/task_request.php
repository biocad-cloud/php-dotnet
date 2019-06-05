<?php

include __DIR__ . "../../../package.php";

dotnet::AutoLoad();

# this get request will shutdown the backend services
# echo var_dump(file_get_contents("http://127.0.0.1:85/?action=shutdown"));

echo var_dump(file_get_contents("http://127.0.0.1:85/?app=hello&echo=world!"));