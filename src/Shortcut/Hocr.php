<?php namespace thiagoalessio\TesseractOCR\Shortcut;

use thiagoalessio\TesseractOCR\Option\Format;

class Hocr
{
	public static function buildOption()
	{
		return new Format('hocr');
	}
}
