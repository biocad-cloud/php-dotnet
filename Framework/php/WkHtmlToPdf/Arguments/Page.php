<?php

namespace PHP\WkHtmlToPdf\Options {

    Imports("System.Object");

    class handlers {
        public const abort  = "abort";
        public const ignore = "ignore";
        public const skip   = "skip";
    }

    /**
     * Page Options
    */
    class Page extends \System\TObject {

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

        /** 
         * Bypass proxy for host (repeatable)
         * @argv --bypass-proxy-for
        */ 
        public $bypassproxyfor;

        /**
        * Web cache directory
        * 
        * 
        * @argv --cache-dir as file
        */ 
        public $cachedir;

        /**
        * Use this SVG file when rendering checked checkboxes
        * 
        * 
        * @argv --checkbox-checked-svg as file
        */ 
        public $checkboxcheckedsvg;

        /**
        * Use this SVG file when rendering unchecked checkboxes
        * 
        * 
        * @argv --checkbox-svg as file
        */ 
        public $checkboxsvg;

        /**
         * Set an additional cookie (repeatable), value should be url encoded.
         * (命令行程序会自动进行url转义编码)
         * 
         * 
         * 
         * @argv --cookie as mixed
        */ 
        public $cookies;

        /**
         * Set an additional HTTP header (repeatable)
         * 
         * 
         * @argv --custom-header as mixed
        */ 
        public $customheader;

        /**
        * Show javascript debugging output
        * 
        * 
        * @argv --debug-javascript as boolean
        */ 
        public $debugjavascript;

        /**
        * Set the default text encoding, for input
        * 
        * 
        * @argv --encoding as string
        */ 
        public $encoding;

        /**
        * Do not make links to remote web pages
        * 
        * 
        * @argv --disable-external-links as boolean
        */ 
        public $disableexternallinks;

        /**
        * Specify how to handle pages that fail to load: abort, ignore Or skip (default abort)
        * 
        * 
        * @argv --load-error-handling as string
        */ 
        public $loaderrorhandling;

        /**
        * Specify how to handle media files that fail To load: abort, ignore Or skip (default ignore)
        * 
        * 
        * @argv --load-media-error-handling as string
        */ 
        public $loadmediaerrorhandling;

        /**
        * Turn HTML form fields into pdf form fields
        * 
        * 
        * @argv --enable-forms as boolean
        */ 
        public $enableforms;

        /**
        * Do not load or print images
        * 
        * 
        * 
        * @argv --no-images as boolean
        */ 
        public $noimages;

        /**
        * Do not make local links
        * 
        * 
        * @argv --disable-internal-links as boolean
        */ 
        public $disableinternallinks;

        /**
        * Do not allow web pages to run javascript
        * 
        * 
        * 
        * @argv --disable-javascript as boolean
        */ 
        public $disablejavascript;

        /**
         * Wait some milliseconds for javascript finish (default 200)
         * 
         * 
         * @argv --javascript-delay as integer
        */ 
        public $javascriptdelay;

        /**
         * Keep relative external links as relative external links
         * 
         * 
         * 
         * @argv --keep-relative-links as boolean
        */ 
        public $keeprelativelinks;

        /**
         * Minimum font size
         * 
         * 
         * @argv --minimum-font-size as integer
        */ 
        public $minimumfontsize;

        /**
         * Set the starting page number (default 0)
         * 
         * 
         * @argv --page-offset as integer
        */ 
        public $pageoffset;

        /**
         * Run this additional javascript after the page is done loading (repeatable)
         * 
         * @argv --run-script as string[]
        */ 
        public $runscript;

        /**
         * Link from section header to toc
         * 
         * @argv --enable-toc-back-links as boolean
        */ 
        public $enabletocbacklinks;

        /**
         * Specify a user style sheet, to load with every page
         * 
         * 
         * @argv --user-style-sheet as file
        */ 
        public $userstylesheet;

        /**
         * Set viewport size if you have custom scrollbars Or css attribute overflow 
         * to emulate window size
         * 
         * @argv --viewport-size as string
        */ 
        public $viewportsize;

        /**
         * Wait until window.status is equal to this String before rendering page
         * 
         * @argv --window-status as string
        */ 
        public $windowstatus;

        /**
         * Use this zoom factor (default 1)
         * 
         * @argv --zoom as double
        */ 
        public $zoom;

        public function ToString() {
            return \Argv::CLIBuilder($this);
        }
    }
}