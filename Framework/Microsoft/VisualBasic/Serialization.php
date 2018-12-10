<?

/**
 * 这个模块之中提供了JSON以及XML序列化和反序列化这两种操作的简写 
 */
class Serialization {

    public static function GetJson(mixed $obj) {
        $json = json_encode($obj);
        return $json;
    }

    public static function LoadObject(string $JSON) {
        $obj = json_decode($JSON);
        return $obj;
    }

    public static function GetXML(mixed $obj) {
        $xml = wddx_serialize_value($obj);
        return $xml;
    }

    public static function FromXML(string $XML) {
        $obj = wddx_deserialize($XML);
        return $obj;
    }

}

?>