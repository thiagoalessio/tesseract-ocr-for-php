<?php namespace thiagoalessio\TesseractOCR\Tests\Unit\Shortcut;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Shortcut\Tsv;

class TsvTest extends TestCase
{
	public function testTsvOption()
	{
		$expected = 'tsv';
		$actual = Tsv::buildOption();
		$this->assertEquals("$expected", "$actual");
	}
}
