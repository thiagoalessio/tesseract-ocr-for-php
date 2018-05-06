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

	public static function userWords($path)
	{
		return function($version) use ($path) {
			if (version_compare($version, '3.04', '<')) {
				$msg = 'user-words option is only available on Tesseract 3.04 or later.';
				$msg.= PHP_EOL."Your version of Tesseract is $version";
				throw new \Exception($msg);
			}
			return '--user-words "'.addcslashes($path, '\\"').'"';
		};
	}

	public static function userPatterns($path)
	{
		return function($version) use ($path) {
			if (version_compare($version, '3.04', '<')) {
				$msg = 'user-patterns option is only available on Tesseract 3.04 or later.';
				$msg.= PHP_EOL."Your version of Tesseract is $version";
				throw new \Exception($msg);
			}
			return '--user-patterns "'.addcslashes($path, '\\"').'"';
		};
	}

	public static function tessdataDir($path)
	{
		return function() use ($path) {
			return '--tessdata-dir "'.addcslashes($path, '\\"').'"';
		};
	}

	public static function lang($languages)
	{
		return function() use ($languages) {
			return '-l '.join('+', $languages);
		};
	}

	public static function config($var, $value)
	{
		return function() use($var, $value) {
			$snakeCase = function($str) {
				return strtolower(preg_replace('/([A-Z])+/', '_$1', $str));
			};
			$pair = $snakeCase($var).'='.$value;
			return '-c "'.addcslashes($pair, '\\"').'"';
		};
	}
}
