<?php namespace thiagoalessio\TesseractOCR;

class Option
{
	public static function psm($psm)
	{
		return function($version) use ($psm) {
			$version = preg_replace('/^v/', '', $version);
			return (version_compare($version, 4, '>=') ? '-' : '')."-psm $psm";
		};
	}

	public static function oem($oem)
	{
		return function($version) use ($oem) {
			Option::checkMinVersion('3.05', $version, 'oem');
			return "--oem $oem";
		};
	}

	public static function dpi($dpi)
	{
		return function() use ($dpi) {
			return "--dpi $dpi";
		};
	}

	public static function userWords($path)
	{
		return function($version) use ($path) {
			Option::checkMinVersion('3.04', $version, 'user-words');
			return '--user-words "'.addcslashes($path, '\\"').'"';
		};
	}

	public static function userPatterns($path)
	{
		return function($version) use ($path) {
			Option::checkMinVersion('3.04', $version, 'user-patterns');
			return '--user-patterns "'.addcslashes($path, '\\"').'"';
		};
	}

	public static function tessdataDir($path)
	{
		return function() use ($path) {
			return '--tessdata-dir "'.addcslashes($path, '\\"').'"';
		};
	}

	public static function lang()
	{
		$languages = func_get_args();
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

	public static function checkMinVersion($minVersion, $currVersion, $option)
	{
		$minVersion = preg_replace('/^v/', '', $minVersion);
		$currVersion = preg_replace('/^v/', '', $currVersion);
		if (!version_compare($currVersion, $minVersion, '<')) return;
		$msg = "$option option is only available on Tesseract $minVersion or later.";
		$msg.= PHP_EOL."Your version of Tesseract is $currVersion";
		throw new \Exception($msg);
	}
}
