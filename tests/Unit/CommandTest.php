<?php namespace thiagoalessio\TesseractOCR\Tests\Unit;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Command;
use thiagoalessio\TesseractOCR\Option\Lang;
use thiagoalessio\TesseractOCR\Option\Psm;

class CommandTest extends TestCase
{
	public function testSimplestCommand()
	{
		$expected = '"tesseract" "image.png" stdout';
		$actual = Command::build('image.png', 'tesseract');
		$this->assertEquals("$expected", "$actual");
	}

	public function testWithSomeOptions()
	{
		$expected = '"tesseract" "image.png" stdout -l eng -psm 6';
		$actual = Command::build('image.png', 'tesseract', [new Lang('eng'), new Psm(6)]);
		$this->assertEquals("$expected", "$actual");
	}

	public function testAppendQuietFlagForTesseract3_03()
	{
		$executable = $this->getFakeTesseract3_03();

		$expected = "\"$executable\" \"image.png\" stdout -psm 3 quiet";
		$actual = Command::build('image.png', $executable, [new Psm(3)]);
		$this->assertEquals("$expected", "$actual");
	}

	private function getFakeTesseract3_03()
	{
		$ext = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? 'bat' : 'sh';
		return "./tests/Unit/fake-tesseract.$ext";
	}
}
