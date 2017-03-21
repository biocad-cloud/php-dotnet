<?php

class Interaction {

    // 执行命令行
    public static function Shell($CLI) {
        return shell_exec($CLI);
    }

}

?>