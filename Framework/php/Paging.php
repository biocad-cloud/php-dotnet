<?php

Imports("php.Utils");

/**
 * 数据库查询数据分页帮助工具
*/
class DbPaging {

    /**
        page = {
            page: data [],        # 当前分页内的数据列表
            total_page: number,   # 总的分页数
            current_page: number, # 当前的分页数
            endOfPage: logical    # 是否已经到达了分页的尾部？
        }
    */

    /**
     * 返回分页数据
     * 
     * @param string|array $tableName 数据表的名字或者数据库配置
     * @param array|integer $id 可以是数字id或者一个数组用来指示id列
     *                          如果$id是一个数字的话，则默认数据表的id列的名称为`id`，则这个$id参数表示start的id编号
     *                          如果$id参数是一个数组的话，则需要传入的形式为：["idName" => start]
     * @param integer $limits 每一页显示的数量
     * @param array|string $condition 这个条件表达式会和id比较之间构成AND关系
    */
    public static function RetrivePage($tableName, $id, $condition = null, $limits = 100) {
        $guid  = "";
        $start = -1;
        
        if (is_numeric($id)) {
            $guid  = "id";
            $start = $id;
        } else {
            list($guid, $start) = Utils::Tuple($id);
        }
        
        $table   = new Table($tableName);
        $maxid   = $table->where($condition)->ExecuteScalar("max(`$guid`)");
        $current = ceil( ($start) / $limits );
        // $pages   = ($maxid - $start) / $limits;
        $pages   = ceil( ($maxid) / $limits );
        
        if ($maxid < $start) {
            # 起始的编号已经超过了最大编号，则肯定没有数据了
            return [
                "page"         => [], 
                "total_page"   => $pages, 
                "current_page" => $current, 
                "endOfPage"    => true,
                "debug"        => $condition
            ];
        } else {

            if (empty($condition)) {
                $condition = [
                    $guid => gt_eq($start)
                ];
            } else {
                if (is_string($condition)) {
                    $condition = "($condition) AND `$guid` >= '$start'";
                } else {
                    $condition[$guid] = gt_eq($start);
                }
            }

            $page = $table->where($condition)
              ->limit($limits) 
              ->order_by([$guid])
              ->select();

            # echo $table->getLastMySql();

            # 将最后一条记录的id和最大的id比较看看当前数据分页
            # 是否已经到达最后一页了
            $endOfPage = Enumerable::Last($page)[$guid] == $maxid;
            
            return [
                "page"         => $page, 
                "total_page"   => $pages, 
                "current_page" => $current, 
                "endOfPage"    => $endOfPage,
                "debug"        => $table->getLastMySql()
            ];
        }
    }
}
?>