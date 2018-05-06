<?php namespace thiagoalessio\TesseractOCR\Shortcut;

use thiagoalessio\TesseractOCR\Option;

class Whitelist
{
	public static function buildOption(...$args)
	{
		$whitelist = '';
		foreach ($args as $arg) {
			$whitelist .= is_array($arg) ? join('', $arg) : $arg;
		}
		return Option::config('tessedit_char_whitelist', $whitelist);
	}
}
