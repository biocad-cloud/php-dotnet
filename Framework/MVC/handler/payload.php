<?php

namespace MVC\Controller {

    interface payload {

        function _has($queryKey, $empty_as_missing = TRUE);
        function _get($queryKey, $default = null);
    }

    class JsonPayload implements \MVC\Controller\payload {

        private $data;

        function __construct($obj) {
            $this->data = $obj;
        }

        function _has($queryKey, $empty_as_missing = TRUE) {
            if (array_key_exists($queryKey, $this->data)) {
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