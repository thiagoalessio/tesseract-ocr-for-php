<?php namespace thiagoalessio\TesseractOCR\Tests\Unit\Option;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Option\Format;

class FormatTest extends TestCase
{
	public function testFormat()
	{
		$expected = 'tsv';
		$actual = new Format('tsv');
		$this->assertEquals("$expected", "$actual");
	}
}
