<?php namespace thiagoalessio\TesseractOCR\Tests\Unit;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Tests\Unit\TestableCommand;
use thiagoalessio\TesseractOCR\Option\Lang;
use thiagoalessio\TesseractOCR\Option\Psm;
use thiagoalessio\TesseractOCR\Command;

class CommandTest extends TestCase
{
	public function testSimplestCommand()
	{
		$cmd = new TestableCommand('image.png');

		$expected = '"tesseract" "image.png" stdout';
		$actual = $cmd->build();
		$this->assertEquals("$expected", "$actual");
	}

	public function testWithSomeOptions()
	{
		$cmd = new TestableCommand('image.png');
		$cmd->options[] = new Lang('eng');
		$cmd->options[] = new Psm(6);

		$expected = '"tesseract" "image.png" stdout -l eng -psm 6';
		$actual = $cmd->build();
		$this->assertEquals("$expected", "$actual");
	}

	public function testAppendQuietFlagForVersion303()
	{
		$executable = $this->getFakeTesseract303();
		$cmd = new Command('image.png');
		$cmd->executable = $executable;
		$cmd->options[] = new Psm(3);

		$expected = "\"$executable\" \"image.png\" stdout -psm 3 quiet";
		$actual = $cmd->build();
		$this->assertEquals("$expected", "$actual");
	}

	private function getFakeTesseract303()
	{
		$ext = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? 'bat' : 'sh';
		return "./tests/Unit/fake-tesseract.$ext";
	}
}
