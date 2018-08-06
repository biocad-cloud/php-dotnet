<?php

Imports("php.Utils");

/**
 * 数据库查询数据分页帮助工具
*/
class DbPaging {

    /*
    
        page = {
            page: data [],
            total_page: number,
            current_page: number,
            endOfPage: logical 
        }

    */

    /**
     * 返回分页数据
     * 
     * @param string $tableName 数据表的名字或者数据库配置
     * @param array|integer $id 可以是数字id或者一个数组用来指示id列
     *                          如果$id是一个数字的话，则默认数据表的id列的名称为`id`，则这个$id参数表示start的id编号
     *                          如果$id参数是一个数组的话，则需要传入的形式为：["idName" => start]
     * @param integer $limits 每一页显示的数量
    */
    public static function RetrivePage($tableName, $id, $limits = 100) {
        $guid  = "";
        $start = -1;
        
        if (is_numeric($id)) {
            $guid  = "id";
            $start = $id;
        } else {
            list($guid, $start) = Utils::Tuple($id);
        }

        $table   = new Table($tableName);
        $maxid   = $table->ExecuteScalar("max(`$guid`)");
        $current = ($start) / $limits;
        $pages   = ($maxid - $start) / $limits;

        if ($maxid < $start) {
            # 起始的编号已经超过了最大编号，则肯定没有数据了
            return [
                "page"         => [], 
                "total_page"   => $pages, 
                "current_page" => $current, 
                "endOfPage"    => true
            ];
        } else {

            $page = $table->where([
                $guid => gt_eq($start)
            ])->limit($limits) 
              ->order_by([$guid])
              ->select();

            # echo $table->getLastMySql();

            $endOfPage = Enumerable::Last($page)[$guid] == $maxid;
            
            return [
                "page"         => $page, 
                "total_page"   => $pages, 
                "current_page" => $current, 
                "endOfPage"    => $endOfPage
            ];
        }
    }
}
?>