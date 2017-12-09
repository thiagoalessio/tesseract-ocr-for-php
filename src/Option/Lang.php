<?php namespace thiagoalessio\TesseractOCR\Option;

class Lang {
    public function __construct() {
        $this->languages = func_get_args();
    }

    public function __toString() {
        return ' -l '.join('+', $this->languages);
    }
}
