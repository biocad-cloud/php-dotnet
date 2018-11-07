<?php

namespace PHP\WkHtmlToPdf\Options {

    Imports ("System.Object");

    class Outline extends \System\TObject {
        
        /**
         * Dump the default TOC xsl style sheet to stdout
         * 
         * @var boolean
         * @argv --dump-default-toc-xsl as boolean
        */
        public $dumpdefaulttocxsl;

        /**
         * Dump the outline to a file
         * 
         * @var string
         * @argv --dump-outline as file
        */
        public $dumpoutline;

        /**
         * Do not put an outline into the pdf
         * 
         * @var boolean
         * @argv --no-outline as boolean
        */
        public $nooutline;

        /**
         * Set the depth of the outline (default 4)
         * 
         * @var integer
         * @argv --outline-depth as integer
        */
        public $outlinedepth;
    }
}