<?php namespace thiagoalessio\TesseractOCR\Tests;

use PHPUnit\Framework\TestCase;
use thiagoalessio\TesseractOCR\TesseractOCR;

class FunctionalTests extends TestCase
{
	private $executable = 'tesseract';
	private $imagesDir  = './tests/images';

	public function setUp()
	{
		if (getenv('TESSERACT_VERSION')) {
			$this->executable = './tests/support/docker-tesseract';
		}
	}

	public function testBasicUsage()
	{
		$expected = join(PHP_EOL, ['The quick brown fox', 'jumps over', 'the lazy dog.']);

		$actual = (new TesseractOCR("{$this->imagesDir}/text.png"))
			->executable($this->executable)
			->run();

		$this->assertEquals($expected, $actual);
	}

	public function testOtherLanguages()
	{
		$expected = 'Bülowstraße';

		$actual = (new TesseractOCR("{$this->imagesDir}/german.png"))
			->executable($this->executable)
			->lang('deu')
			->run();

		$this->assertEquals($expected, $actual);
	}

	public function testMultipleLanguages()
	{
		$expected = 'I eat すし y Pollo';

		$actual = (new TesseractOCR("{$this->imagesDir}/mixed-languages.png"))
			->executable($this->executable)
			->lang('eng', 'jpn', 'spa')
			->run();

		$this->assertEquals($expected, $actual);
	}

	public function testInducingRecognition()
	{
		$expected = 'BOSS';

		$actual = (new TesseractOCR("{$this->imagesDir}/8055.png"))
			->executable($this->executable)
			->whitelist(range('A', 'Z'))
			->run();

		$this->assertEquals($expected, $actual);
	}
}
