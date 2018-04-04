<?php

# include NULL;
include "../package.php";

dotnet::AutoLoad("", TRUE);

Imports("System.Threading.Thread");
Imports("php.Utils");
Imports("Debugger.engine");

use System\Threading\Thread as Thread;

# echo var_dump(dotnetDebugger::GetLoadedFiles());

# echo var_dump(dotnet::$debugger->script_loading);

function sleepTest() {

    echo Utils::Now(FALSE) . "\n\n";

    # 0.5s
    Thread::Sleep(500);

    echo Utils::Now(FALSE) . "\n\n";;

    # 3.5s
    Thread::Sleep(3500);

    echo Utils::Now(FALSE) . "\n\n";;

}

sleepTest();

?>