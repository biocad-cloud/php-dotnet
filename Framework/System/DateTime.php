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

        /**
         * @return DateTime
        */
        public static function DayOfStart() {
            $day = new \System\DateTime();
            $day->Hour   = 0;
            $day->Minute = 0;
            $day->Second = 0;

            return $day;
        }

        /**
         * @return DateTime
        */
        public static function DayOfEnd() {
            $day = new \System\DateTime();
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

        private static function format($n) {
            if ($n < 10) {
                return "0$n";
            } else {
                return $n;
            }
        }
    }
}

?>