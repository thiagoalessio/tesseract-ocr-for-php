<?php namespace thiagoalessio\TesseractOCR\Tests;

use PHPUnit\Framework\TestCase;
use thiagoalessio\TesseractOCR\TesseractOCR;

class UnitTests extends TestCase
{
	public function testSimplestCommand()
	{
		$expected = '"tesseract" "image.png" stdout';

		$actual = (new TesseractOCR('image.png'))
			->buildCommand();

		$this->assertEquals($expected, $actual);
	}

	public function testDefiningLocationOfTesseractExecutable()
	{
		$expected = '"/path/to/tesseract" "image.png" stdout';

		$actual = (new TesseractOCR('image.png'))
			->executable('/path/to/tesseract')
			->buildCommand();

		$this->assertEquals($expected, $actual);
	}

	public function testDefiningLocationOfTessdataDir()
	{
		$expected = '"tesseract" "image.png" stdout --tessdata-dir "/path"';

		$actual = (new TesseractOCR('image.png'))
			->tessdataDir('/path')
			->buildCommand();

		$this->assertEquals($expected, $actual);
	}

	public function testDefiningLocationOfUserWords()
	{
		$expected = '"tesseract" "image.png" stdout'
			.' --user-words "/path/to/user-words.txt"';

		$actual = (new TesseractOCR('image.png'))
			->userWords('/path/to/user-words.txt')
			->buildCommand();

		$this->assertEquals($expected, $actual);
	}

	public function testDefiningLocationOfUserPatterns()
	{
		$expected = '"tesseract" "image.png" stdout'
			.' --user-patterns "/path/to/user-patterns.txt"';

		$actual = (new TesseractOCR('image.png'))
			->userPatterns('/path/to/user-patterns.txt')
			->buildCommand();

		$this->assertEquals($expected, $actual);
	}

	public function testLanguageOption()
	{
		$expected = '"tesseract" "image.png" stdout -l deu';

		$actual = (new TesseractOCR('image.png'))
			->lang('deu')
			->buildCommand();

		$this->assertEquals($expected, $actual);
	}

	public function testLanguageOptionForMultipleLanguages()
	{
		$expected = '"tesseract" "image.png" stdout -l eng+deu+jpn';

		$actual = (new TesseractOCR('image.png'))
			->lang('eng', 'deu', 'jpn')
			->buildCommand();

		$this->assertEquals($expected, $actual);
	}

	public function testPsmOption()
	{
		$expected = '"tesseract" "image.png" stdout -psm 8';

		$actual = (new TesseractOCR('image.png'))
			->psm(8)
			->buildCommand();

		$this->assertEquals($expected, $actual);
	}

	public function testPsmOptionWithValueZero()
	{
		$expected = '"tesseract" "image.png" stdout -psm 0';

		$actual = (new TesseractOCR('image.png'))
			->psm(0)
			->buildCommand();

		$this->assertEquals($expected, $actual);
	}

	public function testConfigOption()
	{
		$expected = '"tesseract" "image.png" stdout'
			.' -c "tessedit_create_pdf=1"'
			.' -c "load_system_dawg=F"';

		$actual = (new TesseractOCR('image.png'))
			->config('tessedit_create_pdf', '1')
			->config('load_system_dawg', 'F')
			->buildCommand();

		$this->assertEquals($expected, $actual);
	}

	public function testConfigSugar()
	{
		$expected = '"tesseract" "image.png" stdout'
			.' -c "tessedit_create_pdf=1"'
			.' -c "load_system_dawg=F"';

		$actual = (new TesseractOCR('image.png'))
			->tesseditCreatePdf(1)
			->loadSystemDawg('F')
			->buildCommand();

		$this->assertEquals($expected, $actual);
	}

	public function testWhitelistSettingShortcutWithMultipleRanges()
	{
		$expected = '"tesseract" "image.png" stdout'
			.' -c "tessedit_char_whitelist=0123456789ABCDEF-_@"';

		$actual = (new TesseractOCR('image.png'))
			->whitelist(range(0, 9), range('A', 'F'), '-_@')
			->buildCommand();

		$this->assertEquals($expected, $actual);
	}

	public function testAppendQuietFlagForVersion303()
	{
		$fakeTesseract = $this->getFakeTesseractExecutable();

		$expected = '"'.addcslashes($fakeTesseract, '\\"').'" "image.png" stdout quiet';

		$actual = (new TesseractOCR('image.png'))
			->executable($fakeTesseract)
			->buildCommand();

		$this->assertEquals($expected, $actual);
	}

	private function getFakeTesseractExecutable()
	{
		$ext = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'bat' : 'sh';
		return join(DIRECTORY_SEPARATOR, [__DIR__, 'support', "fake-tesseract.$ext"]);
	}
}
