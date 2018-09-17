<?php

namespace System\Runtime\Caching {

    class CacheItem {

        /**
         * @var string
        */
        public $Key;
        /**
         * @var mixed
        */
        public $Value;
        /**
         * @var string
        */
        public $RegionName;

        /**
         * @param string $key
         * @param mixed $value
         * @param string $regionName
        */
        public function __construct($key = null, $value = null, $regionName = null) {
            $this->Key = $key;
            $this->Value = $value;
            $this->RegionName = $regionName;
        }
    }
}
?>