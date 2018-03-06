<?php

class REST {

    public function test2() {
        echo json_encode(array(
            "code" => 233,
            "info" => "Hello world!"
        ));
    }
}
?>