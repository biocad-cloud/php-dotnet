<?php

namespace Microsoft\VisualBasic\Data\csv {

    imports("Microsoft.VisualBasic.FileIO.FileSystem");
    imports("Microsoft.VisualBasic.Data.csv.Table");

    /**
     * The csv file I/O extensions
    */
    class Extensions {

        /**
         * Save [key => value] tuples into a csv file.
        */
        public static function SaveNamedValues($array, $path, $title = ["name", "value"], $encoding = "utf8") {
            $data = [];
            $keyName = $title[0];
            $valName = $title[1]; 

            foreach($array as $key => $value) {
                $data[] = [$keyName => $key, $valName => $value];
            }

            return self::SaveTo($data, $path, null, $encoding);
        }

        /**
         * Save array collection as a csv file.
         * 
         * @param array $array The object array collection.
         * @param string $path The saved csv file path.
         * @param callable $project A dictionary table that specific the columns and 
         *                 corresponding field names, column orders, etc.
         * @param string $encoding The csv file text encoding, default is `utf8`.
         * 
         * @return boolean True for file save success, and false not. 
        */
        public static function SaveTo($array, $path, $project = null, $encoding = "utf8", $everyone = TRUE) {
            if (\Strings::Empty($path)) {
                return false;
            } else {
                # 2018-4-10 直接引用其他的模块似乎会因为namespace的缘故而产生错误：
                # <b>Fatal error</b>:  Class 'Microsoft\VisualBasic\Data\csv\FileSystem' not found
                # 所以在这里就直接使用这个函数的代码了
                $directory = dirname($path);
            }

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            } else {
                # do nothing
            }	

            $fp = fopen($path, 'w');

            # 写入第一行标题行
            $project = TableView::FieldProjects($array, $project);
            $project = TableView::Extract($project);
            $fields  = $project["fields"];

            fputcsv($fp, $project["title"]);

            # 写入所有行数据
            foreach ($array as $obj) {
                $list = [];

                # 按照给定的projection投影的顺序进行重排序
                foreach ($fields as $key) {
                    array_push($list, $obj[$key]);
                }

                fputcsv($fp, $list);
            }

            fclose($fp);

            $result = file_exists($path);

            if ($result && $everyone) {
                chmod($path, 0777);
            }

            return $result;
        }

        /**
         * Parse target csv file to an object array.
         * 
         * @param string $path The ``*.csv`` file path.
         * @param string $encoding The file content encoding.
         * 
         * @return array An array of object that read from the given csv file.
        */
        public static function Load($path, 
            $tsv      = false, 
            $maxLen   = 2048, 
            $encoding = "utf8", 
            $headers  = NULL) {

            $file_handle = fopen($path, 'r');
            $delimiter   = $tsv ? "\t" : ",";

            # 2019-03-04 需要注意index问题，当进行header投影的时候
            if (empty($headers)) {
                $headers = fgetcsv($file_handle, $maxLen, $delimiter);
            } else {
                # skip first line headers
                # using header parameter value for column projection
                $devnull = fgetcsv($file_handle, $maxLen, $delimiter);
            }

            if (!file_exists($path) || filesize($path) == 0) {
                \error_log("[\"$path\"] not exists or contains no data!");
                return null;
            } else {
                foreach(self::doParseObjects($file_handle, $headers, 2048, $delimiter) as $obj) {
                    $line_of_text[] = $obj;
                }                
            }

            fclose($file_handle);
            
            return $line_of_text;
        }

        public static function doParseObjects($file_handle, $headers, $maxLen = 2048, $delimiter = ",") {
            while (!feof($file_handle)) {
                $lineText = fgetcsv($file_handle, $maxLen, $delimiter);
                $row = [];

                for ($i = 0; $i < count($headers); $i++) {
                    $row[$headers[$i]] = $lineText[$i];
                }

                if (feof($file_handle)) {
                    # 20191009
                    # 最末尾一般是一个换行符
                    # 所以一般会导致最后一个元素全部都是空值
                    # 在这里处理一下这种情况
                    $allNull = true;

                    foreach($row as $key => $val) {
                        if (!empty($val)) {
                            $allNull = false;
                            break;
                        }
                    }
        
                    if ($allNull) {
                        # 如果全部都是空值，就不返回数据了
                        break;
                    }
                }

                yield $row;
            }
        }

        /**
         * Load tsv table
         * 
         * (这个函数与Load函数的功能基本相同，但是这个函数是用于读取tsv文件的)
         * 
         * @param string $path The file path of the tsv format text file.
        */
        public static function LoadTable($path, $headers = []) {
            $lines = \file_get_contents($path);
            $lines = \trim($lines, "\n\r");
            $lines = \StringHelpers::LineTokens($lines);
            $table = [];

            # 2019-03-04 需要注意index问题，当进行header投影的时候
            if (empty($headers)) {
                $headers = self::ParseTsvRow($lines[0]);
            }

            for($j = 1; $j < count($lines); $j++) {
                $row      = [];
                $lineText = self::ParseTsvRow($lines[$j]);

                for ($i = 0; $i < count($headers); $i++) {
                    $row[$headers[$i]] = $lineText[$i];
                }

                $table[] = $row;
            }

            return $table;
        }

        /** 
         * 解析TSV文件中的一行数据
         * 
         * @return string[]
        */
        public static function ParseTsvRow($line) {
            $tokens = explode("\t", $line);

            for($i = 0; $i < count($tokens); $i++) {
                $t = $tokens[$i];

                if (($t[0] == '"') && ($t[strlen($t) - 1] == '"')) {
                    $t = trim($t, '"');
                }

                $tokens[$i] = $t;
            }

            return $tokens;
        }

        /** 
         * 解析文本文件的首行，将行标题返回
         * 
         * @param string $path 文件的路径
         * @param boolean $tsv 是否是一个TSV文件？
         * 
         * @return string[] 函数返回tsv或者csv文件的行标题
        */
        public static function GetFieldHeaders($path, $tsv = false) {
            $firstLine = \FileSystem::ReadFirstLine($path);

            if ($tsv) {
                return self::ParseTsvRow($firstLine);
            } else {
                return str_getcsv($firstLine); 
            }
        }
    }
}