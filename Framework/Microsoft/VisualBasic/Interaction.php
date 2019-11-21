<?php

class Interaction {

    /**
     * 执行命令行
     * 
     * @param string $CLI 命令行
    */ 
    public static function Shell($CLI) {
        return shell_exec($CLI);
    }

}