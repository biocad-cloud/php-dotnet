<?php

namespace PHP\WkHtmlToPdf\Options {

    class handlers {
        public const abort  = "abort";
        public const ignore = "ignore";
        public const skip   = "skip";
    }

    /**
     * Page Options
    */
    class Page {

        /** 
         * Allow the file or files from the specified folder to be loaded (repeatable)
         * 
         * @argv --allow as file
         * @var string
        */
        public $allow;
        /**
         * Do not print background
         * 
         * @argv --no-background as boolean
         * @var boolean
        */
        public $nobackground;
    }
}