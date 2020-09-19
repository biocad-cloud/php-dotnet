<?php

namespace System {
    
    use \DateTime as Time;

    /**
     * Representation of date and time.
    */
    class DateTime {

        /**
         * @var integer
        */
        public $Year;
        /**
         * @var integer
        */
        public $Month;
        /**
         * @var integer
        */
        public $Day;
        /**
         * @var integer
        */
        public $Hour;
        /**
         * @var integer
        */
        public $Minute;
        /**
         * @var integer
        */
        public $Second;

        /**
         * @param Time $date 一个给定的日期时间，如果这个参数被忽略，
         *     则会创建一个当前的时间点
        */
        public function __construct($date = null) {
            if (empty($date)) {
                $this->Year   = self::year();
                $this->Month  = self::month();
                $this->Day    = self::day();
                $this->Hour   = date("H");
                $this->Minute = date("i");
                $this->Second = date("s");
            } else {
                $this->Year   = $date->format("Y");
                $this->Month  = $date->format("m");
                $this->Day    = $date->format("d");
                $this->Hour   = $date->format("H");
                $this->Minute = $date->format("i");
                $this->Second = $date->format("s");
            }            
        }

        /**
         * @return \DateTime
        */
        public function Date() {
            return new \DateTime($this->ToString());
        }

        public function UnixTimeStamp() {
            return mktime($this->Hour, $this->Minute, $this->Second, $this->Month, $this->Day, $this->Year);
        }

        /**
         * @param string $dateTimeStr
         * 
         * @return DateTime
        */
        public static function Parse($dateTimeStr) {
            return new DateTime(Time::createFromFormat("Y-m-d H:i:s", $dateTimeStr));
        }

        /**
         * @param string|integer
         * @return DateTime
        */
        public static function FromTimeStamp($stamp) {
            if (\is_string($stamp)) {
                $stamp = \strtotime($stamp);
            }

            $date = date('Y-m-d H:i:s', $stamp);
            $date = self::Parse($date);

            return $date;
        }

        /**
         * 获取今天开始的时间点
         * 
         * @param DateTime $day 如果这个参数被忽略掉了，就默认为今天
         * 
         * @return DateTime
        */
        public static function DayOfStart($day = NULL) {
            if (empty($day)) {
                $day = new \System\DateTime();
            }
            
            $day->Hour   = 0;
            $day->Minute = 0;
            $day->Second = 0;

            return $day;
        }

        /**
         * 获取今天结束的时间点
         * 
         * @param DateTime $day 如果这个参数被忽略掉了，就默认为今天
         * 
         * @return DateTime
        */
        public static function DayOfEnd($day = NULL) {
            if (empty($day)) {
                $day = new \System\DateTime();
            }
            
            $day->Hour   = 23;
            $day->Minute = 59;
            $day->Second = 59;

            return $day;
        }

        /**
         * @return DateTime
        */
        public static function Now() {
            return new DateTime();
        }

        public static function year() {
            return date("Y");
        }
    
        public static function month() {
            return date("m");
        }
    
        public static function day() {
            return date("d");
        }

        /**
         * MySql date time style.
         * 
         * @return string
        */
        public function ToString() {
            $year   = self::format($this->Year);
            $month  = self::format($this->Month); 
            $day    = self::format($this->Day);
            $hour   = self::format($this->Hour);
            $minute = self::format($this->Minute);
            $second = self::format($this->Second); 

            return "$year-$month-$day $hour:$minute:$second";
        }

        /**
         * 尝试自动对小于零的数字进行前导零的填充
        */
        private static function format($n) {
            $x = intval($n);

            if ($x < 10) {
                return "0$x";
            } else {
                return $n;
            }
        }

        /**
         * 比较两个时间点的大小，越近的时间越大
         * 
         * @param DateTime $another
         * 
         * @return integer 当前的时间点比较新，1；两个时间相等，0；当前的时间点比较旧，-1
        */
        public function CompareTo($another) {
            $a = $this->UnixTimeStamp();
            $b = $another->UnixTimeStamp();

            if ($a > $b) {
                return 1;                
            } else if ($a < $b) {
                return -1;
            } else {
                return 0;
            }
        }
    }
}