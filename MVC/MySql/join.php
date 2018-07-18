<?php

namespace MVC\MySql\Expression {

    Imports("Microsoft.VisualBasic.Strings");

    /**
     * Syntax:
     * 
     *   tableName => [table1 => field, table2 => field]
    */
    class JoinExpression {

        private static $types = [
            "left_join"  => "LEFT JOIN",
            "right_join" => "RIGHT JOIN",
            "join"       => "JOIN"
        ];

        /**
         * 生成表达式字符串文本
         * 
         * @param array $option tableName => [table1 => field, table2 => field]
         * @param string $type
         * 
         * @return string expression
        */
        public static function AsExpression($option, $type) {           
            $exp  = [];
            $type = self::$types[\Strings::LCase($type)];

            foreach($option as $tableName => $on) {
                $tbls = array_keys($on);
                $tbl1 = $tbls[0]; $tbl1 = "`$tbl1`.`{$on[$tbl1]}`";
                $tbl2 = $tbls[1]; $tbl2 = "`$tbl2`.`{$on[$tbl2]}`";
                
                $s = "`$tableName` ON ($tbl1 = $tbl2)";
                array_push($exp, $s);
            }

            return $type . " " . \Strings::Join($exp, " ");
        }
    }
}

?>