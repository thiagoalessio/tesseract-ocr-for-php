<?php namespace thiagoalessio\TesseractOCR;

class Option
{
	public static function psm($psm)
	{
		return function($version) use ($psm) {
			return (version_compare($version, 4, '>=') ? '-' : '')."-psm $psm";
		};
	}

	public static function oem($oem)
	{
		return function($version) use ($oem) {
			if (version_compare($version, '3.05', '<')) {
				$msg = 'oem option is only available on Tesseract 3.05 or later.';
				$msg.= PHP_EOL."Your version of Tesseract is $version";
				throw new \Exception($msg);
			}
			return "--oem $oem";
		};
	}
}
