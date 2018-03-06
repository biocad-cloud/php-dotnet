<?php

// 定时任务的宿主进程
class taskhost {

    // 任务的时间间隔
    private $interval;
    // 退出工作线程的信号文件的文件路径
    private $signal;

    const ref = __FILE__;

    /**
     * @param interval 线程的休眠时间的间隔长度，单位为秒 
     **/
    public function __construct($signal, $interval = 0) {
        $this->signal   = $signal;
        $this->interval = $interval;        
    }

    public function run($task, $ticks = null) {
        self::signal_on($this->signal);

        while(true == self::check_signal($this->signal)) {

            # 当ticks为空的时候说明不限制，则执行任务
            # 或者当ticks不为空的时候进行检查，如果检查成功则执行任务
            if (!$ticks || self::checkTaskTick($ticks)) {
                $task();
            }            

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

    public static function checkTaskTick($ticks) {
    
        # produces something like 03-12-2012 03:29:13
        $t = date("d-m-Y h:i:s"); 
        # https://stackoverflow.com/questions/4636166/only-variables-should-be-passed-by-reference
        $s = explode(":", $t);
        $s = end($s);

        // 得到秒的最后一个数字
        $s = intval(substr($s, -1));      
        
        foreach($ticks as $t) {
            if ($t == $s) {
                return true;
            }
        }

        return false;
    }
}
?>