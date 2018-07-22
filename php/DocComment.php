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
        $title   = "";
        $summary = "";
        $tags    = [];
        $return  = []; 

        return new DocComment(
            $title, $summary, 
            $tags, 
            $return
        );
    }
}

?>