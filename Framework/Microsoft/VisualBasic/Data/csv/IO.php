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

        public static function delimiterFromFileName($path) {
            $path_parts = pathinfo($path, PATHINFO_EXTENSION);

            if (strtolower($path_parts) != "txt"){
                $separator = ",";
            } else {
                $separator = "\t";
            }

            return $separator;
        }

        /**
         * 将一个csv或者tsv表格文件中的数据以列向量的形式读取出来
         * 
        */
        public static function redCsvColAll($path, $separator = null) {
            $separator = empty($separator) ? self::delimiterFromFileName($path) : $separator;
            $file = new FileFormat($path);
            $keystemp = $file->GetColumnHeaders($separator == "\t");
            $keys = array();
            foreach ($keystemp as $keytemp){
                $keys[] = strtolower($keytemp);
            }

            $filedata = array();
            
            foreach($file->PopulateAllRows($separator == "\t") as $data) {
                for ($x=0; $x < count($keys); $x++) {
                    $key = $keys[$x];

                    if (array_key_exists($key, $filedata)) {
                        $colx = $filedata[$key];
                    } else {
                        $filedata[$keys[$x]] = array();
                        $colx = $filedata[$keys[$x]];
                    }
                    $colx[] = $data[$x];
                    $filedata[$keys[$x]] = $colx;
                }
            }

            $file->Dispose();

            return $filedata;
        }

        public function Dispose() {
            fclose($this->file_handle);
        }
    }
}