<?php namespace thiagoalessio\TesseractOCR\Tests\Unit\Option;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Option\TessdataDir;

class TessdataDirTest extends TestCase
{
	public function testSimplePath()
	{
		$expected = ' --tessdata-dir "/path/to/tessdata"';
		$actual = new TessdataDir('/path/to/tessdata');
		$this->assertEquals("$expected", "$actual");
	}

	public function testPathWithBackslashes()
	{
		$expected = ' --tessdata-dir "c:\\\\path\\\\to\\\\tessdata"';
		$actual = new TessdataDir('c:\path\to\tessdata');
		$this->assertEquals("$expected", "$actual");
	}
}
