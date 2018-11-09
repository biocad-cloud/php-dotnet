<?php

namespace PHP;

Imports("Microsoft.VisualBasic.Extensions.StringHelpers");
Imports("Microsoft.VisualBasic.Strings");
Imports("php.Utils");

/**
 * 解析php的函数注释文档
 * 
 * @author xieguigang
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
     * 模块的作者列表，作者之间使用英文分号进行分割
     * 
     * @var array
    */
    public $authors;

    /**
     * 除了params以及return以外的其他的通过@起始
     * 标记的标签对象数据的数组
     * 
     * @var array
    */
    public $tags;

    /**
     * @param string $title
     * @param string $summary
     * @param array $tags
    */
    function __construct($title, $summary, $tags) {
        $this->title   = $title;
        $this->summary = $summary;    
        $this->tags    = $tags;
        $this->authors = $this->GetDescription("author");
        
        if (!empty($this->authors) || !\Strings::Empty($this->authors)) {
            $authors = explode(";", $this->authors);

            for($i = 0; $i < count($authors); $i++) {
                $authors[$i] = trim($authors[$i]);
            }

            $this->authors = $authors;
        }
    }

    /**
     * @param string $tagName
     * 
     * @return string The description property data in a given tag data
    */
    public function GetDescription($tagName, $default = null) {
        $tagData = \Utils::ReadValue($this->tags, $tagName);

        if (empty($tagData)) {
            return $default;
        } else {
            return \Utils::ReadValue(
                $tagData, "description", $default
            );
        }
    }

    /**
     * Parse doc comment
     * 
     * Parse doc comment into DocComment object
     * 
     * > title, summary, tags
     * 
     * @param string $docComment
     * 
     * @return array
    */
    public static function Parse($docComment) {
        $docComment = \StringHelpers::LineTokens($docComment);
        $docComment = self::Trim($docComment);
        
        $title   = "";
        $summary = "";
        $params  = [];
        $tags    = [];
        $return  = [];
        $i       = 0;

        # 所有不是通过@符号起始的文本行都是描述性的文本段

        # 第一段文本被规定为函数的标题
        list($i, $title)   = \Utils::Tuple(self::blankSplit($docComment, $i));
        # 如果存在第二段文本的话，假设这第二段文本为当前函数的摘要信息
        list($i, $summary) = \Utils::Tuple(self::blankSplit($docComment, $i));

        while($i < count($docComment)) {
            list($i, $table) = \Utils::Tuple(self::tagParser($docComment, $i));
            
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

        $tags["param"]  = $params;
        $tags["return"] = $return;
        $title          = trim($title);
        $summary        = trim($summary);
        
        return [
            "title"   => $title, 
            "summary" => $summary, 
            "tags"    => $tags
        ];
    }

    /**
     * 可以在这里解析所有类型的标签注释
     * 
     * @param array $lines php的注释文档的按照newline进行切割得到的文本行
     * @param integer $i 指向lines参数的行编号，表示这个函数会从这一行开始进行解析
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

        if ($i >= count($lines)) {
            # 已经到达$lines的最末尾
            # 下标越界了
            return [$i => []]; 
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

        $description = trim($description);

        if (!empty($description)) {
            # 假设value是和description之间通过空格进行分割的
            $value = explode(" ", $description);
            $value = $value[0];
        } else {
            $value = "";
        }

        $tagName = \Strings::Mid($tagName, 2);
        $tagData = [
            "name"        => $tagName, 
            "type"        => $type,
            "value"       => $value, 
            "argName"     => $argName, 
            "description" => $description
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

/** 
 * 适用于函数的注释文档的解析器
*/
class MethodDoc extends DocComment {

    /**
     * @var array
    */
    public $params;
    public $return;

    /**
     * @param string $title
     * @param string $summary
     * @param array $tags
    */
    public function __construct($title, $summary, $tags) {
        parent::__construct($title, $summary, $tags);

        $this->params = $tags["param"];
        $this->return = $tags["return"];
    }

    /** 
     * @param string $doc
     * 
     * @return MethodDoc
    */
    public static function ParseMethodDoc($doc) {
        $doc = \PHP\DocComment::Parse($doc);

        return new \PHP\MethodDoc(
            $doc["title"], 
            $doc["summary"], 
            $doc["tags"]
        );
    }
}

/** 
 * 这个类型是MVC框架的控制器专用的
*/
class ControllerDoc extends MethodDoc {

    /**
     * @var string
    */
    public $access;

    /**
     * @param string $title
     * @param string $summary
     * @param array $tags
    */
    public function __construct($title, $summary, $tags) {
        parent::__construct($title, $summary, $tags);

        $this->access = \Utils::ReadValue($tags, "access");
        $this->access = \Utils::ReadValue($this->access, "description");
    }

    /** 
     * @param string $doc
     * 
     * @return ControllerDoc
    */
    public static function ParseControllerDoc($doc) {
        $doc = \PHP\DocComment::Parse($doc);

        return new \PHP\ControllerDoc(
            $doc["title"], 
            $doc["summary"], 
            $doc["tags"]
        );
    }
}

/**
 * 适用于类型定义之中的属性的注释文档的解析器
*/
class PropertyDoc extends DocComment {

    /** 
     * 属性的值类型的标签定义
    */
    public $var;

    /**
     * @param string $title
     * @param string $summary
     * @param array $tags
    */
    public function __construct($title, $summary, $tags) {
        parent::__construct($title, $summary, $tags);

        $this->var = $this->GetDescription("var", "mixed");
    }

    /** 
     * @param string $doc
     * 
     * @return PropertyDoc
    */
    public static function ParsePropertyDoc($doc) {
        $doc = \PHP\DocComment::Parse($doc);

        return new \PHP\PropertyDoc(
            $doc["title"], 
            $doc["summary"], 
            $doc["tags"]
        );
    }

    /**
     * 获取此属性的值类型
     * 
     * @return \System\Type
    */
    public function PropertyType() {
        return \System\Type::GetClass($this->var);
    }
}