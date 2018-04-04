<?php

# include NULL;
include "../package.php";

dotnet::AutoLoad("", TRUE);


echo "A:" . true === APP_DEBUG  .   "  \n\n\n";

dotnet::AutoLoad("", FALSE);

echo "B:" . true === APP_DEBUG  .   "  \n\n\n";

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