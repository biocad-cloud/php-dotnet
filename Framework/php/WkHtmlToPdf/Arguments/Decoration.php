<?php

namespace PHP\WkHtmlToPdf\Options {

    Imports("System.Object");

    /**
     * The page decoration options: Headers And Footer Options
     * 
     * Footers And Headers:
     * Headers And footers can be added to the document by the --header-* And
     * --footer* arguments respectfully.  In header And footer text string supplied
     * to e.g. --header-left, the following variables will be substituted.
     *
     * + [page]       Replaced by the number of the pages currently being printed
     * + [frompage]   Replaced by the number of the first page to be printed
     * + [topage]     Replaced by the number of the last page to be printed
     * + [webpage]    Replaced by the URL of the page being printed
     * + [section]    Replaced by the name of the current section
     * + [subsection] Replaced by the name of the current subsection
     * + [date]       Replaced by the current date in system local format
     * + [isodate]    Replaced by the current date in ISO 8601 extended format
     * + [time]       Replaced by the current time in system local format
     * + [title]      Replaced by the title of the of the current page object
     * + [doctitle]   Replaced by the title of the output document
     * + [sitepage]   Replaced by the number of the page in the current site being converted
     * + [sitepages]  Replaced by the number of pages in the current site being converted
     *
     * As an example specifying --header-right "Page [page] of [toPage]", will result
     * in the text "Page x of y" where x Is the number of the current page And y Is
     * the number Of the last page, To appear In the upper left corner In the document.
    */
    class Decoration extends \System\TObject {

#region "Footers And Headers"

        /**
         * Replaced by the number of the pages currently being printed
         * 
        */
        public const page = "[page]";
        /**
         * Replaced by the number of the first page to be printed
        */
        public const frompage = "[frompage]";
        /**
         * Replaced by the number Of the last page To be printed
        */
        public const topage = "[topage]";
        /**
         * Replaced by the URL Of the page being printed
        */
        public const webpage = "[webpage]";
        /**
         * Replaced by the name Of the current section
        */
        public const section = "[section]";
        /**
         * Replaced by the name Of the current subsection
        */
        public const subsection = "[subsection]";
        /**
         * Replaced by the current Date In system local format
        */
        public const date = "[date]";
        /**
         * Replaced by the current Date In ISO 8601 extended format
        */
        public const isodate = "[isodate]";
        /**
         * Replaced by the current time In system local format
        */
        public const time = "[time]";
        /**
         * Replaced by the title Of the Of the current page Object
        */
        public const title = "[title]";
        /**
         * Replaced by the title Of the output document
        */
        public const doctitle = "[doctitle]";
        /**
         * Replaced by the number Of the page In the current site being converted
        */
        public const sitepage = "[sitepage]";
        /**
         * Replaced by the number Of pages In the current site being converted
         */
        public const sitepages = "[sitepages]";

#endregion

        /**
         * Centered footer/header text
         * 
         * @var string
         * @argv -center as string
        */
        public $center;

        /**
         * Set footer/header font name (default Arial)
         * 
         * @var string
         * @argv -font-name as string
        */
        public $fontname;

        /**
         * Set footer/header font size (default 12)
         * 
         * @var double
         * 
         * @argv -font-size as double
        */
        public $fontsize;

        /**
         * Adds a html footer/header
         * 
         * @var string
         * @argv -html as file
        */
        public $html;

        /**
         * Left aligned footer/header text
         * 
         * @var string
         * 
         * @argv -left as string
        */
        public $left;

        /**
         * Display line above the footer/header
         * 
         * @var boolean
         * 
         * @argv -line as boolean
        */
        public $line;

        /**
         * Right aligned footer/header text
         * 
         * @var string
         * @argv -right as string
        */
        public $right;

        /**
         * Spacing between footer/header and content in mm (default 0)
         * 
         * @var double
         * @argv -spacing as double
        */
        public $spacing;

        public function ToString() {
            return \Argv::CLIBuilder($this);
        }
    }
}