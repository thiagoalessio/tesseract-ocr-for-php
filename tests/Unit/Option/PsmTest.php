<?php namespace thiagoalessio\TesseractOCR\Tests\Unit\Option;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Option\Psm;

class PsmTest extends TestCase
{
	public function testPsm()
	{
		$expected = ' -psm 8';
		$actual = new Psm(8);
		$this->assertEquals("$expected", "$actual");
	}
}
