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

            return $type . " " . \Strings::Join($exp, " $type ");
        }
    }
    
    /**
     * 逻辑表达式的模型
    */
    class LogicalExpression {

        /**
         * 逻辑操作符
        */
        public $operator;
        /**
         * 逻辑表达式字符串数组
        */
        public $expressions;

        function __construct($expressions, $op) {
            $this->operator    = $op;
            $this->expressions = $expressions;
        }

        /**
         * 创建逻辑表达式的模型
         * 
         * @param mixed $a 可以是一个逻辑表达式，或者一个逻辑表达式的向量
         * @param string $b 一个逻辑表达式，当这个参数为空值的时候，$a参数必须是一个数组
         * 
         * @return LogicalExpression 函数返回一个逻辑表达式的模型
        */
        public static function createModel($a, $b, $op) {
            if (!empty($b)) {
                $booleans = [$a, $b];
            } else {
                $booleans = $a;
            }

            return new LogicalExpression($booleans, $op);
        }

        public function Join($key) {
            $exp = [];

            foreach($this->expressions as $expr) {
                $expr = MVC\MySql\Expression\WhereAssert::ValueExpression($expr);
                $expr = "($key $expr)";

                array_push($exp, $expr);
            }

            $exp = join(" {$this->operator} ", $exp);

            return $exp;
        }

        /**
         * 进行值表达式的构建
         * 
         * + 如果是~起始，则说明是一个表达式，则截取第二个字符起始的剩余的字符串之后返回
         * + 如果目标是被两个`````或者``'``符号包裹，则说明是字段的引用或者值，则不做任何处理
         * + 多于其他的任意情况，都会将目标表达式看作为一个值，在值的两边添加``'``符号构成一个值表达式之后返回
         * 
         * @return string SQL语句之中的值表达式
        */
        public static function AutoValue($expr) {
            return MVC\MySql\Expression\WhereAssert::AutoValue($expr);
        }
    }
}