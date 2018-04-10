<?php

namespace Microsoft\VisualBasic\Data\csv {

    Imports("Microsoft.VisualBasic.FileIO.FileSystem");

    class Extensions {

        /**
         * Save array collection as a csv file.
         * 
         * @param array: The object array collection.
         * @param path: The saved csv file path.
         * @param project: A dictionary table that specific the columns and 
         *                 corresponding field names, column orders, etc.
         * @param encoding: The csv file text encoding, default is `utf8`.
         * 
         * @return boolean True for file save success, and false not. 
         */
        public static function SaveTo($array, $path, $project = null, $encoding = "utf8") {
            FileSystem::CreateDirectory(dirname($path));

            $fp = fopen($path, 'w');
          
            # 确保域不是空的，即需要写入csv文件的列不是空集合
            if (!$project) {                
                $project = array();

                for (array_keys($array[0]) as $fieldName) {
                    $project[$fieldName] = $fieldName;
                }
            }

            # 写入第一行标题行
            $names  = array();
            $fields = array();
            foreach ($project as $field => $title) {
                array_push($names,  $title);
                array_push($fields, $field);
            }
            fputcsv($fp, $names);

            # 写入所有行数据
            foreach ($array as $obj) {
                $list = array();

                # 按照给定的projection投影的顺序进行重排序
                foreach ($fields as $key) {
                    array_push($list, $obj[$key]);
                }

                fputcsv($fp, $list);
            }

            fclose($fp);

            return file_exists($path);
        }
    }
}

?>