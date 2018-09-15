<?php

namespace System {
    
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

        public function __construct() {
            $this->Year   = self::year();
            $this->Month  = self::month();
            $this->Day    = self::day();
            $this->Hour   = date("H");
            $this->Minute = date("i");
            $this->Second = date("s");
        }

        /**
         * @return DateTime
        */
        public function DayOfStart() {
            $day = new \System\DateTime();
            $day->Hour   = 0;
            $day->Minute = 0;
            $day->Second = 0;

            return $day;
        }

        /**
         * @return DateTime
        */
        public function DayOfEnd() {
            $day = new \System\DateTime();
            $day->Hour   = 23;
            $day->Minute = 59;
            $day->Second = 59;

            return $day;
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