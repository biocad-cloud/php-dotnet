<?php

# 表达式是使用~符号来进行标记的，其他的起始字符都会将值看作为字符串值

/**
 * 等号，在数据库查询之中判断目标字段是否与目标值相等
*/
function eq($value) { return "~= " . LogicalExpression::AutoValue($value) ; }

/**
 * 不等于，在数据库查询之中判断目标字段是否不与目标值相等
*/
function not_eq($value) { return "~<> " . LogicalExpression::AutoValue($value); }

/**
 * 大于，在数据库查询之中判断目标字段是否大于给定值
*/
function gt($value) { return "~> " . LogicalExpression::AutoValue($value); }

/**
 * 大于等于，在数据库查询之中判断目标字段是否大于等于给定值
*/
function gt_eq($value) { return "~>= " . LogicalExpression::AutoValue($value); }

/**
 * 小于，在数据库查询之中判断目标字段是否小于给定值
*/
function lt($value) { return "~< " . LogicalExpression::AutoValue($value); }

/**
 * 小于等于，在数据库查询之中判断目标字段是否小于等于给定值
*/
function lt_eq($value) { return "~<= " . LogicalExpression::AutoValue($value); }

/**
 * 字符串相似，在数据库查询之中判断目标字段是否和给定的模式相似
*/
function like($value) { return "~LIKE " . LogicalExpression::AutoValue($value); }

/**
 * 字符串不相似，在数据库查询之中判断目标字段是否和给定的模式不相似
*/
function not_like($value) { return "~NOT " . substr(like($value), 1); }

/**
 * 在区间中，在数据库查询之中判断目标字段是否在给定的区间之中
*/
function between($a, $b) { return "~BETWEEN " . LogicalExpression::AutoValue($a) . " AND " . LogicalExpression::AutoValue($b); }

/**
 * 不在区间中，在数据库查询之中判断目标字段是否不存在于给定的区间之中
*/
function not_between($a, $b) { return "~NOT " . substr(between($a, $b), 1); }

# 2018-5-2 
#
# ... 参数数组类型只能够在php7版本使用
# 目前为了兼容php5，将...版本的函数都注释掉先

/**
 * 在集合中，在数据库查询之中判断目标字段值是否在给定的集合中
*/
function in($values) { return "~IN ('". join("', '", $values) ."')"; }
# function in(...$values) { return "~IN ('". join("', '", $values) ."')"; }

/**
 * 不在集合中，在数据库查询之中判断目标字段值是否不再给定的集合中
*/
function not_in($values) { return "~NOT " . substr(in($values), 1); }
# function not_in(...$values) { return "~NOT " . substr(in($values), 1); }

#regin "logical operator"

# 2018-4-25
# Parse error: syntax error, unexpected 'and' (T_LOGICAL_AND), expecting '('
# 函数名不可以直接命名为and/or/xor

/**
 * 对多个逻辑表达式的与运算
 * 
 * @return LogicalExpression
*/
function andalso($a, $b = null) { 
    return LogicalExpression::createModel($a, $b, "AND"); 
}
# function andalso(...$booleans) { return LogicalExpression::Join($booleans, "AND"); }

/**
 * 对多个逻辑表达式的或运算
 * 
 * @return LogicalExpression
*/
function orelse($a, $b = null) { 
    return LogicalExpression::createModel($a, $b, "OR"); 
}

/**
 * 对多个逻辑表达式的异或运算
 * 
 * @return LogicalExpression
*/
function exor($a, $b = null) { 
    return LogicalExpression::createModel($a, $b, "XOR"); 
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

#endregion