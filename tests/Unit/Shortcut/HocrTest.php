<?php namespace thiagoalessio\TesseractOCR\Tests\Unit\Shortcut;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Shortcut\Hocr;

class HocrTest extends TestCase
{
	public function testHocrOption()
	{
		$expected = 'hocr';
		$actual = Hocr::buildOption();
		$this->assertEquals("$expected", "$actual");
	}
}
