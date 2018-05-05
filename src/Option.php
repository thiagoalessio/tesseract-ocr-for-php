<?php namespace thiagoalessio\TesseractOCR;

class Option
{
	public static function psm($psm)
	{
		return function($version) use ($psm) {
			return (version_compare($version, 4, '>=') ? '-' : '')."-psm $psm";
		};
	}
}
