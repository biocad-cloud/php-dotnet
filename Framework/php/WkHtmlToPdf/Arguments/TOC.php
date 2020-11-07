<?php

namespace PHP\WkHtmlToPdf\Options {

    \imports("System.Object");

    class TOC extends \System\TObject {

        /**
         * The header text of the toc (default Table of Contents)
         * 
         * @var string
         * @argv --toc-header-text as string
        */
        public $tocheadertext;

        /**
         * For each level of headings in the toc indent by this length (Default 1em)
         * 
         * @var string
         * @argv --toc-level-indentation as string
        */
        public $toclevelindentation;

        /**
         * Do not use dotted lines in the toc
         * 
         * @var boolean
         * @argv --disable-dotted-lines as boolean
        */
        public $disabledottedlines;

        /**
         * Do not link from toc to sections
         * 
         * @var boolean
         * @argv --disable-toc-links as boolean
        */
        public $disabletoclinks;

        /**
         * For each level of headings in the toc the font Is scaled by this factor (default 0.8)
         * 
         * @var double
         * @argv --toc-text-size-shrink as double
        */
        public $toctextsizeshrink;

        /**
         * Use the supplied xsl style sheet for printing the table Of content
         * 
         * @var string
         * @argv --xsl-style-sheet as file
        */
        public $xslstylesheet;

        public function ToString() {
            return \Argv::CLIBuilder($this);
        }
    }
}