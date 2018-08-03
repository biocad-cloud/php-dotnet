<?php

Imports("Microsoft.VisualBasic.Extensions.StringHelpers");

/**
 * 解析php的函数注释文档
*/
class DocComment {

    /**
     * @var string
    */
    public $title;
    /**
     * @var string
    */
    public $summary;
    /**
     * @var array
    */
    public $params;

    /**
     * 除了params以及return以外的其他的通过@起始
     * 标记的标签对象数据的数组
     * 
     * @var array
    */
    public $tags;
    public $return;

    /**
     * @var string
    */
    public $access;

    /**
     * @param string $title
     * @param string $summary
     * @param array $tags
    */
    function __construct($title, $summary, $tags) {
        $this->title   = $title;
        $this->summary = $summary;    
        $this->tags    = $tags;    
        $this->access  = Utils::ReadValue($tags, "access");
        $this->access  = Utils::ReadValue($this->access, "description");
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

        $doc = new DocComment(trim($title), trim($summary), $tags);
        $doc->params = $params;        
        $doc->return = $return;       

        return $doc;
    }

    /**
     * 
     * @return array [i => [name => ..., type => ..., argName => ..., description => ...]]
    */
    private static function tagParser($lines, $i) {
        while($i < count($lines)) {
            $l = trim($lines[$i]);            

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
        } else if ($tagName == "@return") {
            $description = array_slice($t, 2);
        } else {
            $description = array_slice($t, 1);
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
            "description" => trim($description)
        ];
        
        return [$i => $tagData];
    }

    private static function blankSplit($lines, $i) {
        $text = "";

        while($i < count($lines)) {
            $line = trim($lines[$i]);

            # 如果遇到空白行，就退出
            if (strlen($line) == 0 && strlen(trim($text)) > 0) {
                break;

            # 2018-7-24 bugs修复
            } else if (strlen($line) > 0 && trim($line)[0] == "@") {
                # 如果遇到了标签的起始符
                # 则结束
                break;
            } else {
                $text = $text . " " . $line;
            } 
            
            $i++;
        }

        return [$i => $text];
    }

    /**
     * 函数将左边以及右边的``/``,``*``和空白符号删除
    */
    private static function Trim($doc) {
        for($i = 0; $i < count($doc); $i ++) {

            # 2018-08-03 因为php的版本的问题？
            # 可能会导致注释的解析左边出现空白，导致ltrim失败？
            # 在这里首先将左右的空白都trim掉来解决这个bug
            $line = $doc[$i];
            $line = trim($line);
            $line = ltrim($line, " */");
            
            $doc[$i] = $line;
        }

        return $doc;
    }
}

?>