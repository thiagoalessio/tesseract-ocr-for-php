<?php namespace thiagoalessio\TesseractOCR\Tests\Unit\Option;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Option\Lang;

class LangTest extends TestCase
{
	public function testSingleLanguage()
	{
		$expected = ' -l eng';
		$actual = new Lang('eng');
		$this->assertEquals("$expected", "$actual");
	}

	public function testMultipleLanguages()
	{
		$expected = ' -l eng+deu+jpn';
		$actual = new Lang('eng', 'deu', 'jpn');
		$this->assertEquals("$expected", "$actual");
	}
}
