<?php namespace thiagoalessio\TesseractOCR\Option;

class Config
{
    public function __construct($var, $value)
    {
        $this->var = $var;
        $this->value = $value;
    }

    public function __toString()
    {
        return ' -c "'.addcslashes("{$this->var}={$this->value}", '\\"').'"';
    }
}
