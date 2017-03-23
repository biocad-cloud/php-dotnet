<?php

dotnet::Imports('System.Collection.Generic.Dictionary');

class IndexOf {

    private $__data;

    function __construct($array){
		
        $this->__data = new Dictionary;
        $max = sizeof($array);   

        for($i = 0; $i < $max; $i++) {
            $this->__data->Add($array[$i], $i);
        }

        // echo $this->__data->GetJson();
	}

    /**
     * 函数返回一个整形数，当目标键名$key在字典之中找不到的时候，会返回-1 
     */
    public function IndexOf($key) {

        if($this->__data->ContainsKey($key)) {
            return $this->__data->Item($key);
        } else {
            return -1;
        }

    }

    public function GetJson() {
        return $this->__data->GetJson();
    }
}

?>