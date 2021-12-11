<?php

namespace MVC\MySql\Expression {

    imports("System.Type");

    use \MVC\MySql\SchemaInfo as SchemaDriver;

    class InsertInto {

        /** 
         * Generate Insert Into sql.
         * 
         * @param object $obj
         * @param SchemaDriver 当前的这个表对象的结构信息
         * 
         * @return string
        */
        public static function Sql($any, $schema) {
            // 自增的编号字段
            $auto_increment = $schema->auto_increment;
            $ref    = $schema->ref;
            $fields = [];
            $values = [];

            # Ensure that object $any is an data array
            $data = is_array($any) ? $any : \MVC\MySql\Projector::ToArray($any);
            
            // 使用这个for循环的主要的目的是将所传入的参数数组之中的
            // 无关的名称给筛除掉，避免出现查询错误
            foreach ($schema->schema as $fieldName => $def) {
                if (array_key_exists($fieldName, $data)) {
                    
                    $value = $data[$fieldName];

                    if (\is_string($value)) {
                        $value = str_replace("'", "\'", $value);
                    }
                    
                    # 使用转义函数进行特殊字符串的转义操作
                    # $value = mysqli_real_escape_string($mysqli_exec, $value);
    
                    array_push($fields, "`$fieldName`");
                    array_push($values, "'$value'");
                    
                } else if ($auto_increment && \Strings::LCase($fieldName) == \Strings::LCase($auto_increment) ) {
                    # Do Nothing
                } else {
    
                    # 检查一下这个字段是否是需要值的？如果需要，就将默认值填上
                    if (\Utils::ReadValue($def, "Null", "") == "NO") {
                        # 这个字段是需要有值的，则尝试获取默认值
                        $default = $def["Default"];

                        if ($default) {
                            array_push($fields, "`$fieldName`");
                            array_push($values, "'$default'");
                        } else {
                            # 这个字段需要有值，但是用户没有提供值，而且也不存在默认值
                            # 则肯定无法将这条记录插入数据库
                            # 需要抛出错误？？
                        }
                    }
                }
            }
            
            $fields = join(", ", $fields);
            $values = join(", ", $values);
            
            # INSERT INTO `metacardio`.`xcms_files` (`task_id`) VALUES ('ABC');
            $SQL = "INSERT INTO $ref ($fields) VALUES ($values);";

            return $SQL;
        }
    }
}