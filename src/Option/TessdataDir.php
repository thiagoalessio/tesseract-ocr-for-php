<?php namespace thiagoalessio\TesseractOCR\Option;

class TessdataDir
{
	public function __construct($path)
	{
		$this->path = $path;
	}

	public function __toString()
	{
		return ' --tessdata-dir "'.addcslashes($this->path, '\\"').'"';
	}
}
