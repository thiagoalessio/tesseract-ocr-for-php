<?php namespace thiagoalessio\TesseractOCR\Tests\Unit;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\TesseractOCR;

class TesseractOCRTest extends TestCase
{
	private $command = __NAMESPACE__.'\\TestableCommand';

	public function testSimplestUsage()
	{
		$expected = '"tesseract" "image.png" stdout';
		$actual = (new TesseractOCR('image.png', $this->command))->buildCommand();
		$this->assertEquals("$expected", "$actual");
	}

	public function testCustomExecutablePath()
	{
		$expected = '"/custom/path/to/tesseract" "image.png" stdout';
		$actual = (new TesseractOCR('image.png', $this->command))
			->executable('/custom/path/to/tesseract')
			->buildCommand();
		$this->assertEquals("$expected", "$actual");
	}

	public function testDefiningOptions()
	{
		$expected = '"tesseract" "image.png" stdout -l eng -psm 6 hocr';
		$actual = (new TesseractOCR('image.png', $this->command))
			->lang('eng')
			->psm(6)
			->format('hocr')
			->buildCommand();
		$this->assertEquals("$expected", "$actual");
	}

	public function testDefiningShortcuts()
	{
		$expected = '"tesseract" "image.png" stdout '
			.'-c "tessedit_char_whitelist=0123456789"';
		$actual = (new TesseractOCR('image.png', $this->command))
			->whitelist(range(0, 9))
			->buildCommand();
		$this->assertEquals("$expected", "$actual");
	}

	public function testDefiningConfigPairs()
	{
		$expected = '"tesseract" "image.png" stdout '
			.'-c "load_system_dawg=F" '
			.'-c "tessedit_create_pdf=1"';
		$actual = (new TesseractOCR('image.png', $this->command))
			->loadSystemDawg('F')
			->tesseditCreatePdf(1)
			->buildCommand();
		$this->assertEquals("$expected", "$actual");
	}
}
