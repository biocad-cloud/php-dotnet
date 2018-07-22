<?php

Imports("Microsoft.VisualBasic.Extensions.StringHelpers");

/**
 * 解析php的函数注释文档
*/
class DocComment {

    public $title;
    public $summary;
    public $params;
    public $tags;
    public $return;

    function __construct($title, $summary, $params, $tags, $return) {
        $this->title   = $title;
        $this->summary = $summary;
        $this->tags    = $tags;
        $this->return  = $return;
        $this->params  = $params;
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
        $params  = [];
        $tags    = [];
        $return  = []; 
        $i       = 0;     

        list($i, $title)   = Utils::Tuple(self::blankSplit($docComment, $i));
        list($i, $summary) = Utils::Tuple(self::blankSplit($docComment, $i));

        while($i < count($docComment)) {
            list($i, $table) = Utils::Tuple(self::tagParser($docComment, $i));
            
            if (count($table) > 0) {
                switch($table["name"]) {
                    case "return": 
                        $return = $table;
                        break;
    
                    case "param":
                        $name = $table["argName"];
                        $params[$name] = $table;
                        break;
    
                    default:
                        $tags[$table["name"]] = $table;
                }
            }
        }

        return new DocComment(
            trim($title), trim($summary), 
            $params,
            $tags, 
            $return
        );
    }

    /**
     * 
     * @return array [i => [name => ..., type => ..., argName => ..., description => ...]]
    */
    private static function tagParser($lines, $i) {
        while($i < count($lines)) {
            $l = $lines[$i];            

            if (strlen($l) > 0) {
                break;
            } else {
                $i++;
            }
        }

        $line = trim($lines[$i]);        
        $i++;

        if ($line[0] != "@") {
            return [$i => []];
        }

        $t           = preg_split("#\s+#", $line);
        $tagName     = $t[0];
        $type        = "";
        $argName     = "";
        $description = "";

        if ($tagName == "@param" || $tagName == "@var" || $tagName == "@return") {
            $type = $t[1];
        }
        if ($tagName == "@param") {
            $argName = $t[2];
            $description = array_slice($t, 3);            
        } else {
            $description = array_slice($t, 2);
        }

        $description = implode(" ", $description);

        # 将剩余的字符串也加上去，直到出现@为止
        while($i < count($lines)) {
            if (strlen($lines[$i]) && $lines[$i][0] == "@") {
                break;
            } else {
                $description = $description . "\n" . $lines[$i];
                $i++;
            }
        }

        $tagName = Strings::Mid($tagName, 2);
        $tagData = [
            "name"        => $tagName, 
            "type"        => $type, 
            "argName"     => $argName, 
            "description" => $description
        ];
        
        return [$i => $tagData];
    }

    private static function blankSplit($lines, $i) {
        $text = "";

        while($i < count($lines)) {
            $line = $lines[$i];

            if (strlen($line) == 0 && strlen(trim($text)) > 0) {
                break;
            } else {
                $text = $text . " " . $line;
            } 
            
            $i++;
        }

        return [$i => $text];
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