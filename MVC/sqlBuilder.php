<?php

# 表达式是使用~符号来进行标记的，其他的起始字符都会将值看作为字符串值

/**
 * 等号，在数据库查询之中判断目标字段是否与目标值相等
*/
function eq($value) { return "~= '$value'"; }

/**
 * 不等于，在数据库查询之中判断目标字段是否不与目标值相等
*/
function not_eq($value) { return "~<> '$value'"; }

/**
 * 大于，在数据库查询之中判断目标字段是否大于给定值
*/
function gt($value) { return "~> '$value'"; }

/**
 * 大于等于，在数据库查询之中判断目标字段是否大于等于给定值
*/
function gt_eq($value) { return "~>= '$value'"; }

/**
 * 小于，在数据库查询之中判断目标字段是否小于给定值
*/
function lt($value) { return "~< '$value'"; }

/**
 * 小于等于，在数据库查询之中判断目标字段是否小于等于给定值
*/
function lt_eq($value) { return "~<= '$value'"; }

/**
 * 字符串相似，在数据库查询之中判断目标字段是否和给定的模式相似
*/
function like($value) { return "~LIKE '$value'"; }

/**
 * 字符串不相似，在数据库查询之中判断目标字段是否和给定的模式不相似
*/
function not_like($value) { return "~NOT " . like($value); }

/**
 * 在区间中，在数据库查询之中判断目标字段是否在给定的区间之中
*/
function between($a, $b) { return "~BETWEEN '$a' AND '$b'"; }

/**
 * 不在区间中，在数据库查询之中判断目标字段是否不存在于给定的区间之中
*/
function not_between($a, $b) { return "~NOT " . between($a, $b); }

/**
 * 在集合中，在数据库查询之中判断目标字段值是否在给定的集合中
*/
function in(...$values) { return "~IN ('". join("', '", $values) ."')"; }

/**
 * 不在集合中，在数据库查询之中判断目标字段值是否不再给定的集合中
*/
function not_in(...$values) { return "~NOT " . in($values); }

/**
 * 对多个逻辑表达式的与运算
*/
function and(...$booleans) { return LogicalExpression::Join($booleans, "AND"); }

/**
 * 对多个逻辑表达式的或运算
*/
function or($a, $b) { return LogicalExpression::Join($booleans, "OR"); }

/**
 * 对多个逻辑表达式的异或运算
*/
function xor($a, $b) { return LogicalExpression::Join($booleans, "XOR"); }

class LogicalExpression {

    public static function Join($booleans, $operator) {
        $expression = array_shift($booleans);

        foreach($booleans as $exp) {
            $expression = "($expression) $operator ($exp)";
        }
    
        return $expression;
    }
}

?>