<?php 

/**
 * Xml file handler
 * 
 * > http://php.net/manual/zh/function.xml-parse.php
*/
class XmlParser { 

    #region "XML parser variables"

    var $parser; 
    var $data  = []; 
    var $stack = []; 
    var $keys; 

    #endregion

    // either you pass url atau contents. 
    // Use 'url' or 'contents' for the parameter 
    var $type; 

    /**
     * Passing url/contents
    */
    function __construct($url, $type = 'url') { 
        $this->type = $type; 
        $this->url  = $url; 
        $this->parse(); 
    }
    
    /**
     * parse XML data 
    */
    private function parse() {
        $this->parser = xml_parser_create();
       
        xml_set_object($this->parser, $this); 
        xml_set_element_handler($this->parser, 'startXML', 'endXML'); 
        xml_set_character_data_handler($this->parser, 'charXML');

        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false); 

        if ($this->type == 'url') {
            // if use type = 'url' now we open the XML with fopen 
            $this->parseResource();
        } else if ($this->type == 'contents') {
            // Now we can pass the contents, maybe if you want 
            // to use CURL, SOCK or other method. 
            $this->parseContent();
        } else {
            self::error("Invalid data type!");
        }
    } 

    private function parseContent() {
        $lines = explode("\n", $this->url); 
        $data  = "";
        
        foreach ($lines as $val) { 
            if (trim($val) == '') {
                continue; 
            } else {
                $data = $val . "\n";
            }
            
            if (!xml_parse($this->parser, $data)) {
                $this->throwFileError();
            } 
        } 
    }

    private function throwFileError() {
        $line = xml_get_current_line_number($this->parser);
        $colm = xml_get_current_column_number($this->parser);
        $msg  = 'XML error at line %d column %d';
        $msg  = sprintf($msg, $line, $colm);

        self::error($msg); 
    }

    private function parseResource() {
        if (!($fp = @fopen($this->url, 'rb'))) { 
            self::error("Cannot open {$this->url}"); 
        } 

        $data = ''; 

        while (($data = fread($fp, 8192))) { 
            if (!xml_parse($this->parser, $data, feof($fp))) {
                $this->throwFileError();
            } 
        } 
    }

    private function startXML($parser, $name, $attr) {
        $this->stack[$name] = []; 
        $keys  = ''; 
        $total = count($this->stack) - 1; 
        $i     = 0; 

        foreach ($this->stack as $key => $val) {
            if (count($this->stack) > 1) { 
                if ($total == $i) {
                    $keys .= $key; 
                } else {
                    // The saparator
                    $keys .= $key . '|'; 
                }
            } else {
                $keys .= $key;
            }

            $i++;
        } 

        if (array_key_exists($keys, $this->data)) {
            $this->data[$keys][] = $attr; 
        } else {
            $this->data[$keys] = $attr;
        }
            
        $this->keys = $keys;
    } 

    private function endXML($parser, $name) { 
        end($this->stack);

        if (key($this->stack) == $name) {
            array_pop($this->stack);
        }
    } 

    private function charXML($parser, $data) { 
        if (trim($data) != '') {
            $val = trim(str_replace("\n", '', $data));
            $this->data[$this->keys]['data'][] = $val; 
        }
    } 

    /**
     * 输出错误消息然后退出执行
     * 
     * @param string $msg 错误消息
    */
    public static function error($msg) { 
        echo "
            <div align='center'> 
                <span style='color:red;'>
                    <strong>Error: $msg</strong>
                </span> 
            </div>"; 

        exit(500); 
    } 
} 

?> 