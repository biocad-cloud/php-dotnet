<?php

namespace Microsoft\VisualBasic\Math\Quantile;

Imports("System.Collection.ArrayList");

class Point {
    var $quantile;
    var $error;
    var $u;
    var $v;

    public function __construct($quantile, $error) {
        $this->quantile = $quantile;
        $this->error    = $error;
        $this->u        = 2.0 * $error / (1.0 - $quantile);
        $this->v        = 2.0 * $error / $quantile;
    }
}

class X {
    var $value;
    var $g;
    var $delta;

    public function __construct($value, $lowerDelta, $delta) {
        $this->value = $value;
        $this->g     = $lowerDelta;
        $this->delta = $delta;
    }
}

/**
 * Implementation of the Greenwald and Khanna algorithm for streaming
 * calculation of epsilon-approximate quantiles.
 *  
 * See: 
 * 
 * > Greenwald and Khanna, "Space-efficient online computation of quantile summaries" 
 * > in SIGMOD 2001
*/
class QuantileEstimationGK {

    var $epsilon;
    var $count = 0;
    var $compact_size;

    /** 
     * 按照样本值从小到大进行排序的样本列表
     * 
     * @var \ArrayList
    */
    var $sample;

    /** 
     * @param double[] $data
    */
    public function __construct($data = null, $epsilon = 0.001, $compact_size = 1000) {
        $this->epsilon      = $epsilon;
        $this->compact_size = $compact_size;
        $this->sample       = new \ArrayList();

        if (!empty($data)) {
            foreach($data as $x) {
                $this->insert($x);
            }

            /*
            \console::log("Quantile samples data:");

            foreach($this->sample->GetEnumerator() as $x) {
                \console::log(json_encode($x));
            }
            */
        }
    }

    /** 
     * 插入目标值
     * 
     * 这个函数其实就是一个按照值进行排序插入的操作
     * 
     * @param double $x 所进行插入的样本值
     * @return QuantileEstimationGK 返回当前的统计实例对象
    */
    public function insert($x) {
        $idx   = 0;
        $delta = 0;

        # \console::log("#Begin insert: $x");

        foreach($this->sample->GetEnumerator() as $i) {
            # 得到所需要进行插入的位置
            if ($i->value > $x) {
                break;
            } else {
                $idx++;
            }
        }

        # \console::log("idx=$idx");
        # \console::log("sample_size=" . $this->sample->count());
        # \console::log("#End Insert\n");

        if ($idx == 0 || $idx == $this->sample->count()) {
            $delta = 0;
        } else {
            $delta = (integer) \floor(2 * $this->epsilon * $this->count);
        }

        # 执行样本数据的插入操作
        $this->sample->InsertAt($idx, new X($x, 1, $delta));

        if ($this->sample->count() > $this->compact_size) {
            $this->compress();
        }

        $this->count++;

        return $this;
    }

    private function compress() {
        $removed    = 0;
        $sampleSize = $this->sample->count();
        $bound      = \floor(2 * $this->epsilon * $this->count);

        for($i = 0; $i < $sampleSize - 1; $i++) {
            if ($i == $sampleSize || $i + 1 == $sampleSize) {
                break;
            }

            $x  = $this->sample[$i];
            $x1 = $this->sample[$i + 1];
            
            if ($x->g + $x1->g + $x1->delta <= $bound) {
                $x1->g += $x->g;
                $this->sample->RemoveAt($i);
                $removed++;
            }
        }

        return $removed;
    }

    /** 
     * 查询出百分比位置的值
     * 
     * quantile参数值必须是一个``[0, 1]``之间的小数
     * 
     * @param double $quantile 百分比位置，可以为一个数组，数组参数下会返回一一对应的多个值
    */
    public function query($quantile) {
        if (is_array($quantile)) {
            $out = [];

            foreach($quantile as $q) {
                $out[] = $this->queryImpl($q);
            }

            return $out;
        } else {
            return $this->queryImpl($quantile);
        }
    }

    /** 
     * 进行quantile计算，得到对应位置的样本值
     * 
     * @param double $quantile 所需要进行查询的分位数百分比位置
     * @return double 分位数所对应的样本值
    */
    private function queryImpl($quantile) {
        $rankMin = 0;
        $desired = \floor($quantile * $this->count);
        $desired = $desired + (2 * $this->epsilon * $this->count);
        $n       = $this->sample->count();

        for($i = 1; $i < $n; $i++) {
            $prev     = $this->sample[$i - 1];
            $cur      = $this->sample[$i];
            $rankMin += $prev->g;

            if ($rankMin + $cur->g + $cur->delta > $desired) {
                return $prev->value;
            }
        }

        # 所有的样本值都没有大于预期目标，则当前的这个肯定是所有样本之中的最大值
        # 返回序列的最后一个元素
        return $this->sample->Last()->value;
    }
}