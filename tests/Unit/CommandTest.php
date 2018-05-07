<?php namespace thiagoalessio\TesseractOCR\Tests\Unit;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Tests\Unit\TestableCommand;
use thiagoalessio\TesseractOCR\Option;

class CommandTest extends TestCase
{
	public function testSimplestCommand()
	{
		$cmd = new TestableCommand('image.png');

		$expected = '"tesseract" "image.png" stdout';
		$this->assertEquals("$expected", "$cmd");
	}

	public function testCommandWithOption()
	{
		$cmd = new TestableCommand('image.png');
		$cmd->options[] = Option::lang('eng');

		$expected = '"tesseract" "image.png" stdout -l eng';
		$this->assertEquals("$expected", "$cmd");
	}

	public function testAppendQuietFlagForVersion303()
	{
		$cmd = new TestableCommand('image.png', '3.03');
		$cmd->options[] = Option::psm(3);

		$expected = '"tesseract" "image.png" stdout -psm 3 quiet';
		$this->assertEquals("$expected", "$cmd");
	}

	public function testWithConfigFile()
	{
		$cmd = new TestableCommand('image.png');
		$cmd->configFile = 'hocr';

		$expected = '"tesseract" "image.png" stdout hocr';
		$this->assertEquals("$expected", "$cmd");
	}
}
