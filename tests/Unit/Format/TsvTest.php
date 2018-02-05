<?php namespace thiagoalessio\TesseractOCR\Tests\Unit\Format;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Format\Tsv;

class TsvTest extends TestCase
{
	public function testOutput()
	{
		$expected = ' tsv';
		$actual = new Tsv();
		$this->assertEquals("$expected", "$actual");
	}
}
