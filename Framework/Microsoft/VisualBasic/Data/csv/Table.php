<?php

namespace Microsoft\VisualBasic\Data\csv { 

    class TableView {

        /**
         * Rendering a csv data table to html table.
         * 
         * @param array $data
         * @param boolean $bootstrap Add bootstrap class to the generated html table?
        */
        public static function ToHTMLTable($data, $project = null, $bootstrap = true, $topN = -1, $rotate = false, $columnWidth = NULL) {
            $project = self::FieldProjects($data, $project);
            $project = self::Extract($project);
            $theads = "";

            foreach($project["title"] as $title) {
                $th     = "<th>$title</th>";
                $theads = $theads . $th . "\n";
            }

            $project = $project["fields"];
            $rows    = "";
            $topN    = $topN > 0 ? min($topN, count($data) - 1) : (count($data) - 1);

            for($i = 0; $i < $topN; $i++) {
                $td = "";
                $array = $data[$i];

                foreach($project as $field) {
                    $td = $td . "<td>{$array[$field]}</td>";
                }

                $rows = $rows . "<tr>
                                     $td
                                 </tr>";
            }

            if ($bootstrap) {
                $bootstrap = "table table-hover";
            } else {
                $bootstrap = "";
            }

            if ($rotate) {
                $rotate = "transform:rotate(270deg) !important;";
            } else {
                $rotate = "";
            }

            return "<table class='$bootstrap' style='$rotate'>
                        <thead>
                            <tr>$theads</tr>
                        </thead>
                        <tbody>
                            $rows
                        </tbody>
                    </table>"; 
        }

        public static function ToMarkdownTable($data, $project = null) {

        }

        /**
         * @param mixed $project 如果是字符串数组，则表示对数据对象的垂直投影，反之
         *     则是进行标题的输出，格式为：
         *     1. [string, string, string]
         *     2. [field => title, field => title]
         *     3. "field1|field2|field3"
         *      
        */
        public static function FieldProjects($array, $project) {
            # 确保域不是空的，即需要写入csv文件的列不是空集合
            if (!$project) {
                $project = [];

                foreach (array_keys($array[0]) as $fieldName) {
                    $project[$fieldName] = $fieldName;
                }
            } else if (is_string($project)) {
                # 是 A|B|C|D|E 这种格式
                # 则进行切割
                $fields = [];

                foreach (explode("|", $project) as $fieldName) {
                    $fields[$fieldName] = $fieldName;
                }

                $project = $fields;
            } else if (is_array($project)) {
                
                $fields = [];

                foreach($project as $ref) {
                    if (is_string($ref)) {
                        $fields[$ref] = $ref;
                    } else {
                        list($ref, $title) = \Utils::Tuple($ref);
                        $fields[$ref] = $title;
                    }
                }

                $project = $fields;

            } else {
                throw new \exception("Unsupport data type!");
            }

            return $project;
        }

        /**
         * 函数返回``[fields => [], title => []]``
        */
        public static function Extract($project) {
            $names  = [];
            $fields = [];

            foreach ($project as $field => $title) {
                array_push($names,  $title);
                array_push($fields, $field);
            }

            return [
                "fields" => $fields, 
                "title"  => $names
            ];
        }
    }
}
