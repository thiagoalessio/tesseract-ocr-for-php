<?php namespace thiagoalessio\TesseractOCR\Option;

class UserWords
{
	public function __construct($path)
	{
		$this->path = $path;
	}

	public function __toString()
	{
		return ' --user-words "'.addcslashes($this->path, '\\"').'"';
	}
}
