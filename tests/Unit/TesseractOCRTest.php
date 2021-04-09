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
		$files = glob(join(DIRECTORY_SEPARATOR, array($this->customTempDir, '*')));
		array_map('unlink', $files);
		rmdir($this->customTempDir);
	}

	public function beforeEach()
	{
		$this->tess = new TesseractOCR('image.png', new TestableCommand());
	}

	public function testSimplestUsage()
	{
		$expected = '"tesseract" "image.png" "tmpfile"';
		$actual = $this->tess->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testDelayedSettingOfImagePath()
	{
		$expected = '"tesseract" "image.png" "tmpfile"';

		$ocr = new TesseractOCR(null, new TestableCommand());
		$ocr->image('image.png');
		$actual = $ocr->command;

		$this->assertEquals("$expected", "$actual");
	}

	public function testCustomExecutablePath()
	{
		// skipping for now until I take the time to properly fix it
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') $this->skip();

		$expected = '"/bin/ls" "image.png" "tmpfile"';
		$actual = $this->tess->executable('/bin/ls')->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testDefiningOptions()
	{
		$expected = '"tesseract" "image.png" "tmpfile" -l eng hocr';
		$actual = $this->tess->lang('eng')->format('hocr')->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testAllowlistSingleStringArgument()
	{
		$expected = '"tesseract" "image.png" "tmpfile" -c "tessedit_char_whitelist=abcdefghij"';
		$actual = $this->tess->allowlist('abcdefghij')->command;
		$this->assertEquals("$expected", $actual);
	}

	public function testAllowlistMultipleStringArguments()
	{
		$expected = '"tesseract" "image.png" "tmpfile" -c "tessedit_char_whitelist=abcdefghij"';
		$actual = $this->tess->allowlist('ab', 'cd', 'ef', 'gh', 'ij')->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testAllowlistSingleArrayArgument()
	{
		$expected = '"tesseract" "image.png" "tmpfile" -c "tessedit_char_whitelist=abcdefghij"';
		$actual = $this->tess->allowlist(range('a', 'j'))->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testAllowlistMultipleArrayArguments()
	{
		$expected = '"tesseract" "image.png" "tmpfile" -c "tessedit_char_whitelist=abcdefghij"';
		$actual = $this->tess->allowlist(range('a', 'e'), range('f', 'j'))->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testAllowlistMixedArguments()
	{
		$expected = '"tesseract" "image.png" "tmpfile" -c "tessedit_char_whitelist=0123456789abcdefghij"';
		$actual = $this->tess->allowlist(range(0, 9), 'abcd', range('e', 'j'))->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testDefiningConfigPairs()
	{
		$expected = '"tesseract" "image.png" "tmpfile" '
			.'-c "load_system_dawg=F" '
			.'-c "tessedit_create_pdf=1"';
		$actual = $this->tess->loadSystemDawg('F')->tesseditCreatePdf(1)->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testDefiningConfigFile()
	{
		$expected = '"tesseract" "image.png" "tmpfile" tsv';
		$actual = $this->tess->configFile('tsv')->command;
		$this->assertEquals("$expected", "$actual");
	}

	// @deprecated
	public function testDefiningFormat()
	{
		$expected = '"tesseract" "image.png" "tmpfile" tsv';
		$actual = $this->tess->format('tsv')->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testDigits()
	{
		$expected = '"tesseract" "image.png" "tmpfile" digits';
		$actual = $this->tess->digits()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testHocr()
	{
		$expected = '"tesseract" "image.png" "tmpfile" hocr';
		$actual = $this->tess->hocr()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testPdf()
	{
		$expected = '"tesseract" "image.png" "tmpfile" pdf';
		$actual = $this->tess->pdf()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testQuiet()
	{
		$expected = '"tesseract" "image.png" "tmpfile" quiet';
		$actual = $this->tess->quiet()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testTsv()
	{
		$expected = '"tesseract" "image.png" "tmpfile" tsv';
		$actual = $this->tess->tsv()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testTxt()
	{
		$expected = '"tesseract" "image.png" "tmpfile" txt';
		$actual = $this->tess->txt()->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testCustomTempDir()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') $this->skip();

		$tess = new TesseractOCR('image.png');
		$cmd = $tess->tempDir($this->customTempDir)->command;

		$expected = "\"tesseract\" \"image.png\" \"{$this->customTempDir}";
		$actual = substr("$cmd", 0, strlen($expected));
		$this->assertEquals("$expected", "$actual");
	}

	public function testCustomTempDirWindows()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') $this->skip();

		$customTempDir = 'C:\Users\Foo Bar\Temp\Dir';
		if (!file_exists($customTempDir)) mkdir($customTempDir, null, true);

		$cmd = new Command('image.png');
		$cmd->tempDir = $customTempDir;

		$expected = '"tesseract" "image.png" "C:\Users\Foo Bar\Temp\Dir';
		$actual = substr("$cmd", 0, strlen($expected));
		$this->assertEquals("$expected", "$actual");
	}

	public function testThreadLimit()
	{
		$expected = 'OMP_THREAD_LIMIT=4 "tesseract" "image.png" "tmpfile"';
		$actual = $this->tess->threadLimit(4)->command;
		$this->assertEquals("$expected", "$actual");
	}

	public function testVersion()
	{
		$expected = '3.05';
		$actual = $this->tess->version();
		$this->assertEquals("$expected", "$actual");
	}

	public function testSetOutputFile()
	{
		$expected = '"tesseract" "image.png" "tmpfile" pdf';
		$actual = $this->tess->configFile('pdf')->setOutputFile('/foo/bar.pdf')->command;
		$this->assertEquals("$expected", "$actual");
	}
}
