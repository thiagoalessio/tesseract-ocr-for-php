<?php namespace thiagoalessio\TesseractOCR\Tests\Unit\Format;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Format\Text;

class TextTest extends TestCase
{
	public function testOutput()
	{
		$expected = '';
		$actual = new Text();
		$this->assertEquals("$expected", "$actual");
	}
}
