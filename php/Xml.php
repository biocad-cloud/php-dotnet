<?php 

/**
 * Xml file handler
 * 
 * > http://php.net/manual/zh/function.xml-parse.php
*/
class xx_xml { 

    #region "XML parser variables"

    var $parser; 
    var $name; 
    var $attr; 
    var $data  = []; 
    var $stack = []; 
    var $keys; 
    var $path; 

    #endregion

    // either you pass url atau contents. 
    // Use 'url' or 'contents' for the parameter 
    var $type; 


    // function with the default parameter value 
    function __construct($url='http://www.example.com', $type='url') { 
        $this->type = $type; 
        $this->url  = $url; 
        $this->parse(); 
    }
    
    /**
     * parse XML data 
    */
    function parse() {
        $data = ''; 
        $this->parser = xml_parser_create(); 
        xml_set_object($this->parser, $this); 
        xml_set_element_handler($this->parser, 'startXML', 'endXML'); 
        xml_set_character_data_handler($this->parser, 'charXML'); 

        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false); 

        if ($this->type == 'url') { 
            // if use type = 'url' now we open the XML with fopen 
            
            if (!($fp = @fopen($this->url, 'rb'))) { 
                $this->error("Cannot open {$this->url}"); 
            } 

            while (($data = fread($fp, 8192))) { 
                if (!xml_parse($this->parser, $data, feof($fp))) { 
                    $this->error(sprintf('XML error at line %d column %d', 
                    xml_get_current_line_number($this->parser), 
                    xml_get_current_column_number($this->parser))); 
                } 
            } 
        } else if ($this->type == 'contents') { 
            // Now we can pass the contents, maybe if you want 
            // to use CURL, SOCK or other method. 
            $lines = explode("\n",$this->url); 
            foreach ($lines as $val) { 
                if (trim($val) == '') 
                    continue; 
                $data = $val . "\n"; 
                if (!xml_parse($this->parser, $data)) { 
                    $this->error(sprintf('XML error at line %d column %d', 
                    xml_get_current_line_number($this->parser), 
                    xml_get_current_column_number($this->parser))); 
                } 
            } 
        } 
    } 

    private function startXML($parser, $name, $attr) { 
        $this->stack[$name] = []; 
        $keys  = ''; 
        $total = count($this->stack) - 1; 
        $i     = 0; 

        foreach ($this->stack as $key => $val)    { 
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