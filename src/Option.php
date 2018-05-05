<?php namespace thiagoalessio\TesseractOCR;

class Option
{
	public static function psm($psm)
	{
		return function() use ($psm) {
			return "-psm $psm";
		};
	}
}
