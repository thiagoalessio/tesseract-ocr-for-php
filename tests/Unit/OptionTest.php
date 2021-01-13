<?php namespace thiagoalessio\TesseractOCR\Tests\Unit;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Option;

class OptionTest extends TestCase
{
	public function testPsm()
	{
		$psm = Option::psm(8);
		$this->assertEquals('-psm 8', $psm('3.05.01'));
		$this->assertEquals('--psm 8', $psm('4.0.0-beta.1'));
		$this->assertEquals('--psm 8', $psm('v4.0.0-beta.4.20180912'));
	}

	public function testOem()
	{
		$oem = Option::oem(2);
		$this->assertEquals('--oem 2', $oem('3.05.01'));
		try {
			$oem('3.04.01');
			throw new \Exception('Expected Exception to be thrown');
		} catch (\Exception $e) {
			$expected = 'oem option is only available on Tesseract 3.05 or later.';
			$expected.= PHP_EOL."Your version of Tesseract is 3.04.01";
			$this->assertEquals($expected, $e->getMessage());
		}
	}

	public function testDpi()
	{
		$dpi = Option::dpi(300);

		$this->assertEquals('--dpi 300', $dpi());
	}

	public function testUserWords()
	{
		$userWords = Option::userWords('/path/to/words');
		$this->assertEquals('--user-words "/path/to/words"', $userWords('3.04'));

		$userWords = Option::userWords('c:\path\to\words');
		$this->assertEquals('--user-words "c:\\\\path\\\\to\\\\words"', $userWords('3.04'));

		try {
			$userWords('3.03');
			throw new \Exception('Expected Exception to be thrown');
		} catch (\Exception $e) {
			$expected = 'user-words option is only available on Tesseract 3.04 or later.';
			$expected.= PHP_EOL."Your version of Tesseract is 3.03";
			$this->assertEquals($expected, $e->getMessage());
		}
	}

	public function testUserPatterns()
	{
		$userPatterns = Option::userPatterns('/path/to/patterns');
		$this->assertEquals('--user-patterns "/path/to/patterns"', $userPatterns('3.04'));

		$userPatterns = Option::userPatterns('c:\path\to\patterns');
		$this->assertEquals('--user-patterns "c:\\\\path\\\\to\\\\patterns"', $userPatterns('3.04'));

		try {
			$userPatterns('3.03');
			throw new \Exception('Expected Exception to be thrown');
		} catch (\Exception $e) {
			$expected = 'user-patterns option is only available on Tesseract 3.04 or later.';
			$expected.= PHP_EOL."Your version of Tesseract is 3.03";
			$this->assertEquals($expected, $e->getMessage());
		}
	}

	public function testTessdataDir()
	{
		$tessdataDir = Option::tessdataDir('/path/to/tessdata');
		$this->assertEquals('--tessdata-dir "/path/to/tessdata"', $tessdataDir());

		$tessdataDir = Option::tessdataDir('c:\path\to\tessdata');
		$this->assertEquals('--tessdata-dir "c:\\\\path\\\\to\\\\tessdata"', $tessdataDir());
	}

	public function testLang()
	{
		$lang = Option::lang('eng');
		$this->assertEquals('-l eng', $lang());

		$lang = Option::lang('eng', 'deu', 'jpn');
		$this->assertEquals('-l eng+deu+jpn', $lang());
	}

	public function testConfig()
	{
		$config = Option::config('var', 'value');
		$this->assertEquals('-c "var=value"', $config());

		$config = Option::config('chars', '\'"!$@%&?`');
		$this->assertEquals('-c "chars=\'\\"!$@%&?`"', $config());

		$config = Option::config('fooBarBazChunkyBacon', 'value');
		$this->assertEquals('-c "foo_bar_baz_chunky_bacon=value"', $config());
	}

	public function testCheckMinVersion()
	{
		Option::checkMinVersion('3.05', '4.0.0.20190314', 'option');
		Option::checkMinVersion('3.05', 'v4.0.0.20190314', 'option');
	}
}
