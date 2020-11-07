<?php

namespace PHP\WkHtmlToPdf\Options {

    \imports("System.Object");

    class handlers {
        // public const abort  = "abort";
        // public const ignore = "ignore";
        // public const skip   = "skip";
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
         * 
         * @argv --bypass-proxy-for
        */ 
        public $bypassproxyfor;

        /**
         * Web cache directory
         * 
         * @var string
         * @argv --cache-dir as file
        */ 
        public $cachedir;

        /**
         * Use this SVG file when rendering checked checkboxes
         * 
         * @var string
         * @argv --checkbox-checked-svg as file
        */ 
        public $checkboxcheckedsvg;

        /**
         * Use this SVG file when rendering unchecked checkboxes
         * 
         * @var string
         * @argv --checkbox-svg as file
        */ 
        public $checkboxsvg;

        /**
         * Set an additional cookie (repeatable), value should be url encoded.
         * (命令行程序会自动进行url转义编码)
         * 
         * @var mixed
         * @argv --cookie as mixed
        */ 
        public $cookies;

        /**
         * Set an additional HTTP header (repeatable)
         * 
         * @var mixed
         * @argv --custom-header as mixed
        */ 
        public $customheader;

        /**
         * Show javascript debugging output
         * 
         * @var boolean
         * @argv --debug-javascript as boolean
        */ 
        public $debugjavascript;

        /**
         * Set the default text encoding, for input
         * 
         * @var string
         * @argv --encoding as string
        */ 
        public $encoding;

        /**
         * Do not make links to remote web pages
         * 
         * @var boolean
         * @argv --disable-external-links as boolean
        */ 
        public $disableexternallinks;

        /**
         * Specify how to handle pages that fail to load: abort, ignore Or skip (default abort)
         * 
         * @var string
         * @argv --load-error-handling as string
        */ 
        public $loaderrorhandling;

        /**
         * Specify how to handle media files that fail To load: abort, ignore Or skip (default ignore)
         * 
         * @var string
         * @argv --load-media-error-handling as string
        */ 
        public $loadmediaerrorhandling;

        /**
         * Turn HTML form fields into pdf form fields
         * 
         * @var boolean
         * @argv --enable-forms as boolean
        */ 
        public $enableforms;

        /**
         * Do not load or print images
         * 
         * @var boolean
         * @argv --no-images as boolean
        */ 
        public $noimages;

        /**
         * Do not make local links
         * 
         * @var boolean
         * @argv --disable-internal-links as boolean
        */ 
        public $disableinternallinks;

        /**
         * Do not allow web pages to run javascript
         * 
         * @var boolean
         * @argv --disable-javascript as boolean
        */ 
        public $disablejavascript;

        /**
         * Wait some milliseconds for javascript finish (default 200)
         * 
         * @var integer
         * @argv --javascript-delay as integer
        */ 
        public $javascriptdelay;

        /**
         * Keep relative external links as relative external links
         *
         * @var boolean
         * @argv --keep-relative-links as boolean
        */ 
        public $keeprelativelinks;

        /**
         * Minimum font size
         * 
         * @var integer
         * @argv --minimum-font-size as integer
        */ 
        public $minimumfontsize;

        /**
         * Set the starting page number (default 0)
         * 
         * @var integer
         * @argv --page-offset as integer
        */ 
        public $pageoffset;

        /**
         * Run this additional javascript after the page is done loading (repeatable)
         * 
         * @var string[]
         * @argv --run-script as string[]
        */ 
        public $runscript;

        /**
         * Link from section header to toc
         * 
         * @var boolean
         * @argv --enable-toc-back-links as boolean
        */ 
        public $enabletocbacklinks;

        /**
         * Specify a user style sheet, to load with every page
         * 
         * @var string
         * @argv --user-style-sheet as file
        */ 
        public $userstylesheet;

        /**
         * Set viewport size if you have custom scrollbars Or css attribute overflow 
         * to emulate window size
         * 
         * @var string
         * @argv --viewport-size as string
        */ 
        public $viewportsize;

        /**
         * Wait until window.status is equal to this String before rendering page
         * 
         * @var string
         * @argv --window-status as string
        */ 
        public $windowstatus;

        /**
         * Use this zoom factor (default 1)
         * 
         * @var double
         * @argv --zoom as double
        */ 
        public $zoom;

        public function ToString() {
            return \Argv::CLIBuilder($this);
        }
    }
}