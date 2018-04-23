<?php

namespace MVC\MySql\Expression {

    /**
     * 一个将表达式数组转换为对应的等价的MySql查询条件的工具类
     */
    class WhereAssert {

        /*
            # ThinkPHP之中的Where表达式  

            |TP运算符    |SQL运算符            |例子                                                     |实际查询条件                       |
            |-----------|---------------------|---------------------------------------------------------|---------------------------------|
            |eq         |=                    |$map['id'] = array('eq',100); $map['id'] = 100;          |id = 100                         |
            |neq        |<>                   |$map['id'] = array('neq',100);                           |id <> 100                        |
            |gt         |>                    |$map['id'] = array('gt',100);	                        |id > 100                         |
            |egt        |>=                   |$map['id'] = array('egt',100);                           |id >= 100                        |
            |lt         |<                    |$map['id'] = array('lt',100);	                        |id < 100                         |
            |elt        |<=                   |$map['id'] = array('elt',100);                           |id <= 100                        |
            |like       |LIKE                 |$map['username'] = array('like','Admin%');               |username like 'Admin%'           |
            |between    |BETWEEN .. AND ..    |$map['id'] = array('between','1,8');                     |id BETWEEN 1 AND 8               |
            |not between|NOT BETWEEN .. AND ..|$map['id'] = array('not between','1,8');                 |id NOT BETWEEN 1 AND 8           |
            |in         |IN                   |$map['id'] = array('in','1,5,8');                        |id in(1,5,8)                     |
            |not in     |NOT IN               |$map['id'] = array('not in','1,5,8');                    |id not in(1,5,8)                 | 
            |and（默认） |AND                  |$map['id'] = array(array('gt',1),array('lt',10));        |(id > 1) AND (id < 10)           |
            |or         |OR                   |$map['id'] = array(array('gt',3),array('lt',10), 'or');  |(id > 3) OR (id < 10)            |
            |xor（异或） |XOR                  |两个输入中只有一个是true时，结果为true，否则为false，例子略。|1 xor 1 = 0                      |
            |exp        |expression           |$map['id'] = array('exp','in(1,3,8)');                   |$map['id'] = array('in','1,3,8');|

            注意点：

            1. 默认为等值判断操作 = 
            2. 条件之间默认为AND关系

            $query = M("table")->Where(array(
                "flag" => 0,
                "id"   => eq(100),
                "id"   => between(10, 20)
            ))->limit(100)
              ->select();

            Using ... to access variable arguments  php 5.6+

            function sum(...$numbers) {
                $acc = 0;
                foreach ($numbers as $n) {
                    $acc += $n;
                }
                return $acc;
            }

            echo sum(1, 2, 3, 4);

        */

        /**
         * 将条件数组转化为MySQL之中的条件表达式 
         * 
         * @param asserts: 条件数组
         * @param op: 条件之间的相互关系，默认为AND关系
         * 
         * @return string MySql查询条件表达式
         */
        public static function AsExpression($asserts, $op = "AND") {
            $expressions = array();

            foreach ($asserts as $key => $value) {
                $assert = self::AsExpressionInternal($key, $value);
                array_push($expressions, $assert);
            }

            return "(" . implode(") $op (", $expressions) . ")";
        }

        private static function AsExpressionInternal($key, $value) {
            # 第一个字符可能是!或者~
            $c      = $key[0];
            $assert = NULL;

            switch($c) {
                case "!":

                    $key = self::Pop($key);

                    if (is_array($value)) {
                        # NOT IN
                        $assert = self::AsIN($key, $value, true);
                    } else {
                        # NOT equals
                        $assert = "`$key` <> '$value'";
                    }

                    break;

                case "~":

                    $key = self::Pop($key);
                    $not = $key[0];

                    if ($not == "!") {
                        # not LIKE
                        $key    = self::Pop($key);
                        $assert = "`$key` NOT LIKE '$value'";
                    } else {
                        # LIKE
                        $assert = "`$key` LIKE '$value'";
                    }

                    break;

                default:

                    # 默认操作为最基本的值等价判断操作    
                    # 字典对象则是IN值等价判断操作
                    # 单独的值对象则是直接的IS等价判断操作
                    if (is_array($value)) {
                        # IN
                        $assert = self::AsIN($key, $value);
                    } else {
                        # equals
                        $assert = "`$key` = '$value'";
                    }
            }
        }

        private static function PopulateFieldAsserts($key, $assert) {
            $chars = str_split($string);
            $buffer = array();
            $field = "";
            $asserts = "";

            foreach($chars as $c) {
                if ($c == "|" || $c == "&") {

                    $op = ($c == "|") ? "OR" : "AND";
                    $field = implode("", $buffer);
                    $buffer = array();
                    $asserts = $asserts . "`$field` $assert $op "; 

                } else {
                    array_push($buffer, $c);
                }
            }

            # 正确的语法是  key1|key2|key3 这样子的模式
            # 则按照这个语法，在退出循环之后肯定会在buffer里面存在key的字符
            $field = implode("", $buffer);                    
            $asserts = $asserts . "`$field` $assert"; 

            return $asserts;
        }

        /**
         * 删除key字符串的第一个操作符字符 
         * 
         * @param key: 数据库表之中的字段名称
         * 
         * @return string 函数返回删除掉第一个操作符字符的字段名称
         */
        private static function Pop($key) {
            return substr($key, 1);
        }

        /**
         * 将数组之中的一个键值对转换为IN表达式 
         * 
         * @param field: 数据表之中的字段名称
         * @param array: 进行IN操作符所需要的右边的值列表      
         * @param not: 是否是NOT取反操作？
         * 
         */
        public static function AsIN($field, $array, $not = false) {
            $value = join("', '", $array);		
            
            if ($not) {
                return "`$field` NOT IN ('$value')";
            } else {
                return "`$field`     IN ('$value')";
            }
        }
    }
}

?>