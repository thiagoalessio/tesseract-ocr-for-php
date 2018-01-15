<?php namespace thiagoalessio\TesseractOCR\Option;

class UserPatterns
{
	public function __construct($path)
	{
		$this->path = $path;
	}

	public function __toString()
	{
		return ' --user-patterns "'.addcslashes($this->path, '\\"').'"';
	}
}
