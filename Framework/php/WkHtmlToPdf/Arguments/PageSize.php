<?php

namespace PHP\WkHtmlToPdf\Options {

    Imports("System.Object");
    Imports("System.Enum");

    /**
     * The default page size of the rendered document is A4, but using this
     * --page-size optionthis can be changed to almost anything else, such as A3,
     * Letter And Legal. For a full list of supported pages sizes please see
     * &lt;http: //qt-project.org/doc/qt-4.8/qprinter.html#PaperSize-enum>.
     *
     * For a more fine grained control over the page size the --page-height And
     * --page-width options may be used
     * 
     * This enum type specifies what paper size QPrinter should use. QPrinter does 
     * not check that the paper size is available; it just uses this information, 
     * together with QPrinter::Orientation and ``QPrinter::setFullPage()``, 
     * to determine the printable area.
     *
     * The defined sizes (With setFullPage(True)) are
    */
    class QPrinter extends \Enum {

        public const A0        = 5;  // 841 x 1189 mm
        public const A1        = 6;  // 594 x 841 mm
        public const A2        = 7;  // 420 x 594 mm
        public const A3        = 8;  // 297 x 420 mm
        public const A4        = 0;  // 210 x 297 mm, 8.26 x 11.69 inches
        public const A5        = 9;  // 148 x 210 mm
        public const A6        = 10; // 105 x 148 mm
        public const A7        = 11; // 74 x 105 mm
        public const A8        = 12; // 52 x 74 mm
        public const A9        = 13; // 37 x 52 mm
        public const B0        = 14; // 1000 x 1414 mm
        public const B1        = 15; // 707 x 1000 mm
        public const B2        = 17; // 500 x 707 mm
        public const B3        = 18; // 353 x 500 mm
        public const B4        = 19; // 250 x 353 mm
        public const B5        = 1;  // 176 x 250 mm, 6.93 x 9.84 inches
        public const B6        = 20; // 125 x 176 mm
        public const B7        = 21; // 88 x 125 mm
        public const B8        = 22; // 62 x 88 mm
        public const B9        = 23; // 33 x 62 mm
        public const B10       = 16; // 31 x 44 mm
        public const C5E       = 24; // 163 x 229 mm
        public const Comm10E   = 25; // 105 x 241 mm, U.S. Common 10 Envelope
        public const DLE       = 26; // 110 x 220 mm
        public const Executive = 4;  // 7.5 x 10 inches, 190.5 x 254 mm
        public const Folio     = 27; // 210 x 330 mm
        public const Ledger    = 28; // 431.8 x 279.4 mm
        public const Legal     = 3;  // 8.5 x 14 inches, 215.9 x 355.6 mm
        public const Letter    = 2;  // 8.5 x 11 inches, 215.9 x 279.4 mm
        public const Tabloid   = 29; // 279.4 x 431.8 mm
        public const Custom    = 30; // Unknown, Or a user defined size.

        /** 
         * QPrinter enum value to string
        */
        public static function ToString($value) {
            return \Enum::ToString($value);
        }

        /** 
         * Enum name to value parser
        */
        public static function TryParse($name) {
            return \Enum::TryParse($name);
        }
    }

    class PageSize extends \System\TObject {

        /**
         * 如果这个参数为<see cref="QPrinter.Custom"/>，则还需要指定width和height
         * 
         * @var string
         * @argv --page-size as string
        */
        public $pagesize = QPrinter::A4;

        /**
         * @argv --page-width as double
        */        
        public $pagewidth;

        /** 
         * @argv --page-height as double
        */
        public $pageheight;

        public function ToString() {
            $pagesize = \PHP\WkHtmlToPdf\Options\QPrinter::ToString($this->pagesize);

            if ($this->pagesize == QPrinter::Custom) {
                return "--page-size \"{$pagesize}\" --page-width {$this->pagewidth} --page-height {$this->pageheight}";
            } else {
                return "--page-size \"{$pagesize}\"";
            }
        }
    }
}