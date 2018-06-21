<?php

Imports("Microsoft.VisualBasic.Strings");

class MSDN {

    /**
     * URL link about data type help
    */
    public static function link($fullName, $lang = "en-us") {
        $fullName = Strings::LCase($fullName);
        return "https://msdn.microsoft.com/$lang/library/$fullName(v=vs.110).aspx?cs-save-lang=1&cs-lang=vb";
    }
}
?>