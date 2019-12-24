<?php

imports("Microsoft.VisualBasic.FileIO.FileSystem");
imports("Microsoft.VisualBasic.Language.Value.Uid");

/**
 * 定时任务的宿主进程
*/
class taskhost {

    /**
     * 任务的时间间隔
     * 
     * @var integer 
    */ 
    private $interval;
    /** 
     * 退出工作线程的信号文件的文件路径
     * 
     * @var string 
    */ 
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
     * @var Uid
    */
    static $uid;

    /** 
     * @return string
    */
    public static function getNextTempName() {
        if (empty(self::$uid)) {
            self::$uid = new Uid();
        }

        $id = self::$uid->Add()->ToString();
        $id = str_pad($id, 7, "0", STR_PAD_LEFT);
        $id = "TEMP$id";

        return $id;
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

    /**
     * 运行R脚本的帮助函数
     * 
     * @param string|ScriptBuilder $R The R script file path or its text content in string or stringbuilder type.
     *      (所将要运行的目标R语言脚本的文件路径或者文本内容)
     * @param string $workspace 运行R脚本的工作区路径，如果R脚本参数是
     *   脚本文本内容而非路径的话，则脚本会被保存在这个工作区参数所指定的
     *   文件夹之中
    */
    public static function RunRscript($R, $workspace, $log = "Rscript.log", $R_HOME = "/usr/lib64/R/bin") {
        $Rscript = "$R_HOME/Rscript";
        $current = getcwd();

        if (!file_exists($workspace)) {
            mkdir($workspace, 0777, true);
        }

        if (!is_string($R) && (get_class($R) == "ScriptBuilder")) {
            $R = $R->ToString();
        }

        if (strpos($R, "\n") > -1 || !file_exists($R)) {
            $tempName = taskhost::getNextTempName();
            $path     = "$workspace/Rscript/$tempName.R";
            
            # write script file and using its file path
            # as reference
            FileSystem::WriteAllText($path, $R);
            $R = $path;
        }

        # 切换至目标工作区
        chdir($workspace);

        // 下面的if是为了兼容云服务器和本地服务器
        // 因为二者上面的的R程序的文件位置可能不一样
        if (file_exists($Rscript)) {
            $CLI = "$Rscript --no-save --no-restore --verbose \"{$R}\" > {$workspace}/$log 2>&1";
        } else {            
            // 因为没有找到Rscript程序，则可能是因为运行的环境变了的原因，
            // 所以在这里就直接通过Rscript命令来执行操作
            // 假若已经在bash的环境之中添加了Rscript程序所处的文件夹路径的话，
            // 这段代码是能够被正常的执行的
            $CLI = "Rscript --no-save --no-restore --verbose \"{$R}\" > {$workspace}/$log 2>&1";
        }

        console::log($CLI);

        shell_exec($CLI);
        # 切换回之前的工作区文件夹
        chdir($current);
    }
}