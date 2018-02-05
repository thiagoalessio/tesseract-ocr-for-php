<?php namespace thiagoalessio\TesseractOCR\Tests\Unit\Format;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Format\Hocr;

class HocrTest extends TestCase
{
	public function testOutput()
	{
		$expected = ' hocr';
		$actual = new Hocr();
		$this->assertEquals("$expected", "$actual");
	}
}
