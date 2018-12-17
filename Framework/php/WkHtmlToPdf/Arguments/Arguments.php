<?php

namespace PHP\WkHtmlToPdf {

    class PdfOutput {

        /** 
         * @var string
        */
        public $OutputFilePath; 
        /** 
         * 这个属性是一个二进制raw字符串
         * 
         * @var string
        */
        public $OutputStream; 

        /** 
         * @var callable
        */
        public $OutputCallback; 
    }    

    class PdfConvertEnvironment {
        public $TempFolderPath;
        public $WkHtmlToPdfPath;

        /**
         * @var integer
        */
        public $Timeout;

        /**
         * @var boolean
        */
        public $Debug;
    }    
}