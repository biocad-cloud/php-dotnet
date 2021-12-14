<?php

namespace MVC\Controller {

    interface payload {

        function _has($queryKey, $empty_as_missing = TRUE);
        function _get($queryKey, $default = null);
    }

    class JsonPayload implements \MVC\Controller\payload {

        private $data;
        private $is_array;

        function __construct($obj) {
            if (!is_array($obj)) {
                $this->data = get_object_vars($obj);
            } else {
                $this->data = $obj;
            }
        }

        function _has($queryKey, $empty_as_missing = TRUE) {            
            if ($this->is_array && array_key_exists($queryKey, $this->data)) {
                return $empty_as_missing ? $this->data[$queryKey] != "" : TRUE;            
            } else {
                return FALSE;
            }
        }

        function _get($queryKey, $default = null) {
            if (array_key_exists($queryKey, $this->data)) {
                return $this->data[$queryKey];
            } else {
                return $default;
            }
        }
    }
}