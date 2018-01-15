<?php namespace thiagoalessio\TesseractOCR\Option;

class Psm
{
	public function __construct($psm)
	{
		$this->psm = $psm;
	}

	public function __toString()
	{
		return " -psm {$this->psm}";
	}
}
