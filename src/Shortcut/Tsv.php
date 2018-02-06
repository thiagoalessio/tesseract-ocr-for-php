<?php namespace thiagoalessio\TesseractOCR\Shortcut;

use thiagoalessio\TesseractOCR\Option\Format;

class Tsv
{
	public static function buildOption()
	{
		return new Format('tsv');
	}
}

