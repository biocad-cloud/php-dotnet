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

            Using ... to access variable arguments  php 5.6+

            function sum(...$numbers) {
                $acc = 0;
                foreach ($numbers as $n) {
                    $acc += $n;
                }
                return $acc;
            }

            echo sum(1, 2, 3, 4);


            $query = M("table")->Where([
                "flag"    => 0,
                "id|uid"  => eq(100),
                "balance" => between(10, 20)
            ])->or(["email" => like("%@gmail.com")])
              ->limit(100)
              ->distinct(["id", "year"])
              ->select();

            SELECT DISTINCT id, year 
            FROM `table` 
            WHERE (flag = 0 
                AND (id = 100 OR uid = 100) 
                AND balance between 10 AND 20) 
            OR (email LIKE '%@gmail.com') 
            LIMIT 100;

        */

        /**
         * 将条件数组转化为MySQL之中的条件表达式 
         * 
         * @param asserts: 条件数组
         * @param op: 条件之间的相互关系，默认为AND关系
         * 
         * @return string MySql查询条件表达式
         */
        public static function AsExpression($asserts) {

        }
    }
}

?>