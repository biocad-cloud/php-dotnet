<?php

namespace MVC\MySql\Expression {

    class WhereAssert {

        /*
          
          IS
          array("key" => "value"):  `key` = 'value'
          array("key" => array()):  `key` IN (...)

          Not IS
          array("!key" => "value"): `key` <> 'value'
          array("!key" => array()): `key` NOT IN (...)

          LIKE and Not LIKE
          array("~key" => "value"): `key` LIKE 'value'
          array("~!key" => "value"): `key` NOT LIKE 'value'

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

                        if (is_array($value)) {
                            # IN
                            $assert = self::AsIN($key, $value);
                        } else {
                            # equals
                            $assert = "`$key` = '$value'";
                        }
                }

                array_push($expressions, $assert);
            }

            return join(" $op ", $expressions);
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