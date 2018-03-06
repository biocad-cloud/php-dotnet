<?php

// 定时任务的宿主进程
class taskhost {

    // 任务的时间间隔
    private $interval;
    // 退出工作线程的信号文件的文件路径
    private $signal;

    /**
     * @param interval 线程的休眠时间的间隔长度，单位为秒 
     **/
    public function __construct($signal, $interval = 0) {
        $this->signal   = $signal;
        $this->interval = $interval;        
    }

    public function run($task) {
        self::signal_on($this->signal);

        while(true == self::check_signal($signal)) {
            $task();

            if ($this->interval > 0) {
                sleep($this->interval);
            }
        }
    }

    public static function check_signal($signal, $check = "on") {
        if (!file_exists($signal)) {
            return true;
        } else {
            return preg_match("/$check/i", file_get_contents($signal));
        }
    }

    public static function set_signal($signal, $flag) {
        file_put_contents($signal, $flag);
    }

    public static function signal_on($signal) {
        taskhost::set_signal($signal, "on");
    }

    public static function signal_off($signal) {
        taskhost::set_signal($signal, "off");
    }
}
?>