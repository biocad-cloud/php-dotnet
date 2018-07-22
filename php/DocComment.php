<?php

Imports("Microsoft.VisualBasic.Extensions.StringHelpers");

/**
 * 解析php的函数注释文档
*/
class DocComment {

    public $title;
    public $summary;
    public $tags;
    public $return;

    function __construct($title, $summary, $tags, $return) {
        $this->title   = $title;
        $this->summary = $summary;
        $this->tags    = $tags;
        $this->return  = $return;
    }

    /**
     * Parse doc comment
     * 
     * Parse doc comment into DocComment object
     * 
     * @param string $docComment
     * 
     * @return DocComment
    */
    public static function Parse($docComment) {
        $docComment = StringHelpers::LineTokens($docComment);
        $docComment = self::Trim($docComment);
        $title   = "";
        $summary = "";
        $tags    = [];
        $return  = []; 
        $i       = 0;

        while($i < count($docComment)) {
            $line = $docComment[$i];

            if (strlen($line) == 0) {
                break;
            } else {
                $title = $title . " " . $line;
            }            
        }

        while($i < count($docComment)) {
            $line = $docComment[$i];

            if (strlen($line) == 0) {
                break;
            } else {
                $summary = $summary . " " . $line;
            }            
        }        

        return new DocComment(
            trim($title), trim($summary), 
            $tags, 
            $return
        );
    }

    private static function Trim($doc) {
        for($i = 0; $i < count($doc); $i ++) {
            $line = $doc[$i];
            $line = ltrim($line, " */");
            $line = rtrim($line);

            $doc[$i] = $line;
        }

        return $doc;
    }
}

?>