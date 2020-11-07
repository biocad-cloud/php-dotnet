<?php

namespace PHP\WkHtmlToPdf\Options {

    \imports("System.Object");

    class GlobalOptions extends \System\TObject {

        /**
         * Do not collate when printing multiple copies
         * 
         * @var boolean
         * @argv --no-collate as boolean 
        */
        public $nocollate;

        /**
         * Read and write cookies from and to the supplied cookie jar file
         * 
         * @var string
         * @argv --cookie-jar as file
        */
        public $cookiejar;

        /**
         * Number of copies to print into the pdf file (default 1)
         * 
         * @var integer
         * @argv --copies as integer
        */
        public $copies;

        /**
         * Change the dpi explicitly (this has no effect on X11 based systems) 
         * (default 96)
         * 
         * @var integer
         * @argv --dpi as integer
        */
        public $dpi;

        /**
         * PDF will be generated in grayscale
         * 
         * @var boolean
         * @argv --grayscale as boolean 
        */
        public $grayscale;

        /**
         * When embedding images scale them down to this dpi(default 600)
         * 
         * @var integer
         * @argv --image-dpi as integer
        */
        public $imagedpi;

        /**
         * When jpeg compressing images use this quality (default 94)
         * 
         * @var integer
         * @argv --image-quality as integer
        */
        public $imagequality;

        /**
         * Generates lower quality pdf/ps. Useful to shrink the result document space
         * 
         * @var boolean
         * @argv --lowquality as boolean 
        */
        public $lowquality;

        /**
         * Set the page bottom margin
         * 
         * @var string
         * @argv --margin-bottom as string
        */
        public $marginbottom;

        /**
         * Set the page left margin (default 10mm)
         * 
         * @var string
         * @argv --margin-left as string
        */
        public $marginleft;

        /**
         * Set the page right margin (default 10mm)
         * 
         * @var string
         * @argv --margin-right as string
        */
        public $marginright;

        /**
         * Set the page top margin
         * 
         * @var string
         * @argv --margin-top as string
        */
        public $margintop;

        /**
         * Set orientation to Landscape or Portrait (default Portrait)
         * 
         * @var string
         * @argv --orientation as string
        */
        public $orientation;

        /**
         * Do not use lossless compression on pdf objects
         * 
         * @var boolean
         * @argv --no-pdf-compression as boolean 
        */
        public $nopdfcompression;

        /**
         * The title of the generated pdf file (The title of the first document 
         * Is used if Not specified)
         * 
         * @var string
         * @argv --title as string
        */
        public $title;

        public function ToString() {
            return \Argv::CLIBuilder($this);
        }
    }
}