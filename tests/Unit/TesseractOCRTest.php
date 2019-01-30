<?php namespace thiagoalessio\TesseractOCR\Tests\Unit;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\TesseractOCR;
use thiagoalessio\TesseractOCR\Command;
use thiagoalessio\TesseractOCR\Tests\Unit\TestableCommand;

class TesseractOCRTest extends TestCase
{
	public function setUp()
	{
		$this->customTempDir = __DIR__.DIRECTORY_SEPARATOR.'custom-temp-dir';
		mkdir($this->customTempDir);
	}

	public function tearDown()
	{
		$files = glob(join(DIRECTORY_SEPARATOR, [$this->customTempDir, '*']));
		array_map('unlink', $files);
		rmdir($this->customTempDir);
	}

	public function beforeEach()
	{
		$this->tess = new TesseractOCR('image.png', new TestableCommand());
	}

	public function testSimplestUsage()
	{
		$expected = '"tesseract" "image.png" tmpfile';
		$actual = $this->tess->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testDelayedSettingOfImagePath()
	{
		$expected = '"tesseract" "image.png" tmpfile';

		$ocr = new TesseractOCR(null, new TestableCommand());
		$ocr->image('image.png');
		$actual = $ocr->command;

		$this->assertEquals("$expected", "$actual");
	}

	public function testCustomExecutablePath()
	{
		$expected = '"/custom/path/to/tesseract" "image.png" tmpfile';
		$actual = $this->tess->executable('/custom/path/to/tesseract')->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testDefiningOptions()
	{
		$expected = '"tesseract" "image.png" tmpfile -l eng hocr';
		$actual = $this->tess->lang('eng')->format('hocr')->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testWhitelistSingleStringArgument()
	{
		$expected = '"tesseract" "image.png" tmpfile -c "tessedit_char_whitelist=abcdefghij"';
		$actual = $this->tess->whitelist('abcdefghij')->command;
		$this->assertEquals("$expected", $actual);
	}

	public function testWhitelistMultipleStringArguments()
	{
		$expected = '"tesseract" "image.png" tmpfile -c "tessedit_char_whitelist=abcdefghij"';
		$actual = $this->tess->whitelist('ab', 'cd', 'ef', 'gh', 'ij')->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testWhitelistSingleArrayArgument()
	{
		$expected = '"tesseract" "image.png" tmpfile -c "tessedit_char_whitelist=abcdefghij"';
		$actual = $this->tess->whitelist(range('a', 'j'))->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testWhitelistMultipleArrayArguments()
	{
		$expected = '"tesseract" "image.png" tmpfile -c "tessedit_char_whitelist=abcdefghij"';
		$actual = $this->tess->whitelist(range('a', 'e'), range('f', 'j'))->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testWhitelistMixedArguments()
	{
		$expected = '"tesseract" "image.png" tmpfile -c "tessedit_char_whitelist=0123456789abcdefghij"';
		$actual = $this->tess->whitelist(range(0, 9), 'abcd', range('e', 'j'))->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testDefiningConfigPairs()
	{
		$expected = '"tesseract" "image.png" tmpfile '
			.'-c "load_system_dawg=F" '
			.'-c "tessedit_create_pdf=1"';
		$actual = $this->tess->loadSystemDawg('F')->tesseditCreatePdf(1)->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testDefiningConfigFile()
	{
		$expected = '"tesseract" "image.png" tmpfile tsv';
		$actual = $this->tess->configFile('tsv')->command;
		$this->assertEquals("$expected", "$actual");
	}

	// @deprecated
	public function testDefiningFormat()
	{
		$expected = '"tesseract" "image.png" tmpfile tsv';
		$actual = $this->tess->format('tsv')->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testDigits()
	{
		$expected = '"tesseract" "image.png" tmpfile digits';
		$actual = $this->tess->digits()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testHocr()
	{
		$expected = '"tesseract" "image.png" tmpfile hocr';
		$actual = $this->tess->hocr()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testPdf()
	{
		$expected = '"tesseract" "image.png" tmpfile pdf';
		$actual = $this->tess->pdf()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testQuiet()
	{
		$expected = '"tesseract" "image.png" tmpfile quiet';
		$actual = $this->tess->quiet()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testTsv()
	{
		$expected = '"tesseract" "image.png" tmpfile tsv';
		$actual = $this->tess->tsv()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testTxt()
	{
		$expected = '"tesseract" "image.png" tmpfile txt';
		$actual = $this->tess->txt()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testCustomTempDir()
	{
		$cmd = (new TesseractOCR('image.png'))
			->tempDir($this->customTempDir)
			->command;

		$expected = "\"tesseract\" \"image.png\" {$this->customTempDir}";
		$actual = substr("$cmd", 0, strlen($expected));
		$this->assertEquals("$expected", "$actual");
	}

	public function testThreadLimit()
	{
		$expected = 'OMP_THREAD_LIMIT=4 "tesseract" "image.png" tmpfile';
		$actual = $this->tess->threadLimit(4)->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testVersion()
	{
		$expected = '3.05';
		$actual = $this->tess->version();
		$this->assertEquals("$expected", "$actual");
	}
}
