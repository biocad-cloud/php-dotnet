<?php

dotnet::Imports("../ICollection.php");

/**
 * A dynamics array object.
 */
class List extends ICollection {

    public function Add($obj) {
        array_push($this->__data;, $obj);
    }

    public function AddRange($array) {
        foreach($array  as $obj) {
            $this->Add($obj);
        }
    }

    public function Remove($obj) {
        user_error("This method have not implemented yet!");
    }

    public function InsertAt(int $index, $obj) {
        user_error("This method have not implemented yet!");
    }
}

?>