<?php

namespace Microsoft\VisualBasic\Data\csv {

    class FileFormat implements \System\IDisposable {

        /** 
         * The csv file path
         * 
         * @var string
        */
        private $filepath;
        /** 
         * The file stream
         * 
         * @var resource
        */
        private $file_handle;
        private $firstLine;

        /** 
         * @param string $path The file path
        */
        public function __construct($path, $encoding = "utf8") {
            $this->filepath = $path;
            $this->file_handle = fopen($path, 'r');
            $this->firstLine = fgets($this->file_handle);
			# 右边肯定会存在一个\r或者\n换行符，在这里将其删除
			$this->firstLine = rtrim($this->firstLine, "\r\n");
        }

        public function GetColumnHeaders($tsv = false) {
            if ($tsv) {
                return Extensions::ParseTsvRow($this->firstLine);
            } else {
                return str_getcsv($this->firstLine); 
            }
        }

        public function PopulateAllRows($tsv = false, $maxLen = 2048) {
            $delimiter = $tsv ? "\t" : ",";

            while (!feof($this->file_handle)) {
                yield fgetcsv($this->file_handle, $maxLen, $delimiter);
            }
        }

        public function Dispose() {
            fclose($this->file_handle);
        }
    }
}