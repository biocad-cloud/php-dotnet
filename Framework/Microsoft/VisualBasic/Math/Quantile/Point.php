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
     * @var \ArrayList
    */
    var $sample;

    /** 
     * @param double[] $data
    */
    public function __construct($epsilon, $compact_size, $data = null) {
        $this->epsilon      = $epsilon;
        $this->compact_size = $compact_size;
        $this->sample       = new \ArrayList();

        if (!empty($data)) {
            foreach($data as $x) {
                $this->insert($x);
            }
        }
    }

    /** 
     * @param double $x 
     * @return QuantileEstimationGK
    */
    public function insert($x) {
        $idx   = 0;
        $delta = 0;

        foreach($this->sample as $i) {
            if ($i->value > $x) {
                break;
            } else {
                $idx++;
            }
        }

        if ($idx == 0 || $idx == $this->sample->count()) {
            $delta = 0;
        } else {
            $delta = (integer) \floor(2 * $this->epsilon * $this->count);
        }

        $this->sample->Add(new X($x, 1, $delta));

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

    public function query($quantile) {
        $rankMin = 0;
        $desired = (integer) \floor($quantile * $this->count);
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

        return $this->sample->Last()->value;
    }
}