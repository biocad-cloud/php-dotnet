<?php

class CommandLine implements ArrayAccess {

    /**
     * The program command name, if there are multiple command defined in your php script.
    */
    public $name;
    /**
     * The parameter array of the command
     * 
     * @var array
    */
    public $arguments;
    /**
     * The file path of the php script file.
     * 
     * @var string
    */
    public $script;

    function __construct($name = null, $arguments = null, $script = null) {
        $this->name      = $name;
        $this->arguments = $arguments;
        $this->script    = $script;
    }

    #region "implements ArrayAccess"

    public function offsetSet($offset, $value) {        
        $this->arguments[$offset] = $value;   
    }

    public function offsetExists($offset) {        
        if (array_key_exists($offset, $this->arguments)) {
            return true;
        } else {
            return !empty($this->findArgumentOffset(ltrim($offset, "-/")));
        }
    }

    private function findArgumentOffset($trimmed) {
        foreach($this->arguments as $key => $val) {
            if (ltrim($key, "-/") === $trimmed) {
                return $key;
            }
        }

        return null;
    }

    public function offsetUnset($offset) {
        if (array_key_exists($offset, $this->arguments)) {
            unset($this->arguments[$offset]);
        } else {
            $key = $this->findArgumentOffset(ltrim($offset, "-/"));

            if (!empty($key)) {
                unset($this->arguments[$key]);
            }            
        }        
    }

    public function offsetGet($offset) {
        if (isset($this->arguments[$offset])) {
            return $this->arguments[$offset];    
        } else {
            $key = $this->findArgumentOffset(ltrim($offset, "-/"));

            if (!empty($key)) {
                return $this->arguments[$key];
            } else {
                return null;
            }
        }
    }

    #endregion

    /**
     * Get argument value in the commandline.
     * 
     * @param string $default The default parameter value if the argument 
     *       name is not exists in commandline input.
     * @param string $name The argument name in the commandline input.
    */
    public function getArgumentValue($name, $default = null) {
        if ($this->offsetExists($name)) {
            return $this->offsetGet($name);
        } else {
            return $default;
        }
    }
}