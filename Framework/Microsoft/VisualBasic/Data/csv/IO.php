<?php

namespace Microsoft\VisualBasic\Data\csv {

    imports("Microsoft.VisualBasic.Data.csv.Extensions");

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
         * @var integer
        */
        private $maxLen;

        /** 
         * @param string $path The file path
        */
        public function __construct($path, $encoding = "utf8", $maxLen = 4096) {
            $this->filepath = $path;
            $this->file_handle = fopen($path, 'r');
            $this->firstLine = fgets($this->file_handle);
			# 右边肯定会存在一个\r或者\n换行符，在这里将其删除
            $this->firstLine = rtrim($this->firstLine, "\r\n");
            $this->maxLen = $maxLen;

            if (false === $this->file_handle) {
                \console::error("the given file '$path' is not found on your file system!");
            }
        }

        public function isValid() {
            return !(false === $this->file_handle);
        }

        /**
         * 从已缓存的首行字符串文本数据中解析出所有的列标题
        */
        public function GetColumnHeaders($tsv = false) {
            if ($tsv) {
                return Extensions::ParseTsvRow($this->firstLine);
            } else {
                return str_getcsv($this->firstLine); 
            }
        }

        /**
         * 尝试从行首的字符推断出文件的格式
         * 
         * @return boolean 文件是否为tsv文件
        */
        public function MeasureTsvFormat() {
            $comma = 0;
            $tab   = 0;
            $header = $this->firstLine;

            for($i = 0; $i < strlen($header); $i++) {
                $c = $header[$i];

                if ($c == ",") {
                    $comma++;
                } else if ($c == "\t") {
                    $tab++;
                }
            }

            return $tab > $comma;
        }

        /**
         * @param string $file;
         * 
         * @return boolean 目标文件是否为一个tsv文件
        */
        public static function isTsv($file) {
            return using(new FileFormat($file), function($table) {
                return $table->MeasureTsvFormat();
            });
        }

        /**
         * 查看所给定的表头是否都存在于当前的这个表格文件之中
         * 
         * 如果这个函数返回空值，说明没有不存在的表头
         * 
         * @param string[] 所需要进行检查的目标表头字符串的集合
         * 
         * @return 这个函数会返回不存在的表头
        */
        public function checkHeaderExists($headersToCheck, $tsv = false) {
            $tableHeaders = $this->GetColumnHeaders($tsv);
            $nonExists = [];

            foreach($headersToCheck as $title) {
                if (!in_array($title, $tableHeaders)) {
                    $nonExists[] = $title;
                }
            }

            if (count($nonExists) == 0) {
                return null;
            } else {
                return $nonExists;
            }
        }

        public function isEOF() {
            if (!$this->isValid()) {
                return true;
            } else {
                return feof($this->file_handle);
            }
        }

        /**
         * @param boolean $asObject 是否以对象集合的形式返回所有的行数据，这个函数默认是返回原始字符串数组的
        */
        public function PopulateAllRows($tsv = false, $asObject = false) {
            $delimiter = $tsv ? "\t" : ",";

            if (!$this->isValid()) {
                \console::error("returns empty row collection due to the reason of missing target data file: [{$this->filepath}]!");
                return [];
            }

            if ($asObject) {
                foreach(Extensions::doParseObjects(
                    $this->file_handle, 
                    $this->GetColumnHeaders($tsv), 
                    $this->maxLen, 
                    $delimiter
                ) as $obj) {

                    # 20191009 直接使用return关键词返回yield表达式的数据源生成函数
                    # 会出现bug，所以在这里还需要使用一个for循环来产生数据
                    yield $obj;
                }
            } else {
                while (!feof($this->file_handle)) {
                    yield fgetcsv($this->file_handle, $this->maxLen, $delimiter);
                }
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

        public static function readStringMatrix($path, $separator = null) {
            $separator = empty($separator) ? self::delimiterFromFileName($path) : $separator;
            $file = new FileFormat($path);
            $matrix = [$file->GetColumnHeaders($separator == "\t")];

            foreach($file->PopulateAllRows($separator == "\t") as $data) {
                $matrix[] = $data;
            }

            return $matrix;
        }

        public function getColumnIndex($col, $separator) {
            $index = -1;
            $col = strtolower($col);           

            foreach ($this->GetColumnHeaders($separator == "\t") as $keytemp){
                $index++;
                
                if ($col == strtolower($keytemp)) {
                    break;                
                }
            }

            return $index;
        }

        /**
         * 将一个csv文件的某一指定的列中的数据读取出来
         * 
         * @param string|integer $col 列标题或者列索引号
         * @param string $separator
         * 
         * @return string[] 以数组的形式返回目标列的数据
        */
        public function readStringVector($col, $separator = null) {
            $separator = empty($separator) ? self::delimiterFromFileName($this->filepath) : $separator;

            if (\is_integer($col)) {
                $index = $col;
            } else {    
                $index = $this->getColumnIndex($col, $separator);
            } 
            
            $data = [];
            
            foreach($this->PopulateAllRows($separator == "\t") as $row) {
                if ((empty($row) || (count($row) == 0)) && $this->isEOF()) {
                    // do nothing
                } else {
                    $data[] = $row[$index];
                }               
            }

            return $data;
        }

        /**
         * 将一个csv文件的某一指定的列中的数据读取出来
         * 
         * @param string|integer $col 列标题或者列索引号
         * @param string|boolean $separator 分隔符，当为逻辑值的时候，true表示为tsv文件，反之为csv文件
         * 
         * @return array 以数组的形式返回目标列的数据
        */
        public static function readCsvColVector($path, $col, $separator = null) {
            if (empty($separator)) {
                $separator = empty($separator) ? self::delimiterFromFileName($path) : $separator;
            } else if (is_bool($separator)) {
                $separator = $separator ? "\t" : ",";
            } else if (is_string($separator)) {
                // do nothing
            } else {
                \dotnet::ThrowException("Unsupported separator!");
            }
            
            return using(new FileFormat($path), function($table) use ($col, $separator) {
                return $table->readStringVector($col, $separator);
            });        
        }

        /**
         * 将一个csv或者tsv表格文件中的数据以列向量的形式读取出来
         * 
        */
        public static function readCsvColAll($path, $separator = null) {
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