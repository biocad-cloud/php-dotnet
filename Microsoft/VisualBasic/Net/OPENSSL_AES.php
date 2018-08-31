<?php

/**
 * AES-128-CBC
*/
class AES128CBC {

    /**
     * SSL加密的算法名称
    */
    const cipher = "AES-128-CBC";

    /**
     * 初始向量的长度
     * 
     * @var integer
    */
    var $ivlen;
    /**
     * 密匙
     * 
     * @var string
    */
    var $key;

    /**
     * 构建一个用于数据加密以及解密的模块
     * 
     * @param string $key 要求字符串的长度必须为16的整数倍，并且应该为ASCII字符串
    */
    public function __construct($key) {
        $this->$key  = $key;
        $this->ivlen = openssl_cipher_iv_length($cipher = AES128CBC::cipher);

        console::log("AES-128-CBC iv-length={$this->ivlen}");
    }

    public function Encrypt($message) {
        $iv         = openssl_random_pseudo_bytes($this->ivlen);
        $ciphertext = openssl_encrypt(
            $message, 
            AES128CBC::cipher, 
            $this->key, 
            $options = OPENSSL_RAW_DATA, 
            $iv
        );
        $ciphertext = "$iv|$ciphertext";
        $ciphertext = base64_encode($ciphertext);

        return $ciphertext;
    }

    public function Decrypt($ciphertext) {
        $ciphertext = base64_decode($ciphertext);
        $iv         = substr($ciphertext, 0, $this->ivlen);
        $ciphertext = substr($ciphertext, $this->ivlen + 1);
        $message    = openssl_decrypt(
            $ciphertext, 
            AES128CBC::cipher, 
            $this->key, 
            $options = OPENSSL_RAW_DATA, 
            $iv
        );

        return $message;
    }
}