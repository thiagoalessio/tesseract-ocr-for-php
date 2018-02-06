<?php namespace thiagoalessio\TesseractOCR\Option;

class Format
{
	public function __construct($format='')
	{
		$this->format = $format;
	}

	public function __toString()
	{
		return $this->format;
	}
}
