<?php namespace thiagoalessio\TesseractOCR\Option;

class Lang
{
	public function __construct(...$languages)
	{
		$this->languages = $languages;
	}

	public function __toString()
	{
		return ' -l '.join('+', $this->languages);
	}
}
