<?php

class Interaction {

    /**
     * 执行命令行
     * 
     * @param string $command 命令行
    */ 
    public static function Shell($command) {
        $tempName = Utils::RandomASCIIString(16);
        $dirName  = dotnet::getMyTempDirectory() . "/rscript_std";
        $stdout   = "$dirName/$tempName.txt";
        $command  = $command . " > $stdout 2>&1";

        FileSystem::CreateDirectory($dirName);

        $devnull = shell_exec($command);
        $output  = file_get_contents($stdout);

        return $output;
    }
}