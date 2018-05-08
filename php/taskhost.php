<?php

/**
 * 定时任务的宿主进程
*/
class taskhost {

    // 任务的时间间隔
    private $interval;
    // 退出工作线程的信号文件的文件路径
    private $signal;

    const ref = __FILE__;

    /**
     * 创建一个新的后台任务主机进程实例
     * 
     * @param integer interval 线程的休眠时间的间隔长度，单位为秒 
     * @param string $signal 这个新的宿主实例接受外部关闭信号的信号文件位置
    */
    public function __construct($signal, $interval = 0) {
        $this->signal   = $signal;
        $this->interval = $interval;        
    }

    /**
     * 运行一个周期性执行的后台任务
     * 
     * @param function $task 需要周期性执行的后台任务的函数指针
     * @param array $ticks 如果这个数组参数不为空，那么后台任务只在秒数的最后一位数字
     *                     为这个数组之中给定的数字的时候才会被执行，这个函数参数是用来
     *                     进行多线程的并发控制的
    */
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

    /**
     * 检查信号文件之中的内容是否是等于给定的check参数内容？
     * 
     * @param string $signal 信号文件的文件路径
     * @param string $check 信号内容
    */
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

    /**
     * 检查当前的时间点，秒数的最后一位数字是否是给定的$ticks数组之中的一个数字
     * 
     * @param array $ticks 一个整形数数组，表示时间点的秒数的最后一位数字
    */
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