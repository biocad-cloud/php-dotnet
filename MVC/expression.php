<?php

namespace MVC\MySql\Expression {

    /**
     * 一个将表达式数组转换为对应的等价的MySql查询条件的工具类
     */
    class WhereAssert {

        /*
          
          IS
          array("key" => "value"):  `key` = 'value'
          array("key" => array()):  `key` IN (...)

          Not IS
          array("!key" => "value"): `key` <> 'value'
          array("!key" => array()): `key` NOT IN (...)

          LIKE and Not LIKE
          array("~key"  => "value"): `key` LIKE 'value'
          array("~!key" => "value"): `key` NOT LIKE 'value'

          # 这个表达式可能会对表进行模糊搜索匹配有用
          AND/OR
          array("key1|key2|key3"   => "value"): `key1` = 'value' OR `key2` = 'value' OR `key3` = 'value'
          array("key1&key2&key3"   => "value"): `key1` = 'value' AND `key2` = 'value' AND `key3` = 'value'
          array("~key1|key2|key3"  => "%value%"): `key1` LIKE '%value%' OR `key2` LIKE '%value%' OR `key3` LIKE '%value%'
          array("!key1|key2|key3"  => "value"): NOT (`key1` = 'value' OR `key2` = 'value' OR `key3` = 'value')
          array("!key1&key2&key3"  => "value"): NOT (`key1` = 'value' AND `key2` = 'value' AND `key3` = 'value')
          array("~!key1&key2&key3" => "%value%"): NOT (`key1` LIKE '%value%' AND `key2` LIKE '%value%' AND `key3` LIKE '%value%')

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