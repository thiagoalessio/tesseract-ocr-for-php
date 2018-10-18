<?php namespace thiagoalessio\TesseractOCR\Tests\EndToEnd;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\TesseractOCR;

class ReadmeExamples extends TestCase
{
	private $executable = 'tesseract';
	private $imagesDir  = './tests/EndToEnd/images';

	public function testBasicUsage()
	{
		$expected = "The quick brown fox\njumps over\nthe lazy dog.";
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
		// training data for this old version returns different output
		if ($this->isVersion302()) $this->skip();

		$expected = 'I eat すし y Pollo';
		$actual = (new TesseractOCR("{$this->imagesDir}/mixed-languages.png"))
			->executable($this->executable)
			->lang('eng', 'jpn', 'spa')
			->run();
		$this->assertEquals($expected, $actual);
	}

	public function testInducingRecognition()
	{
		// https://github.com/tesseract-ocr/tesseract/issues/751
		if ($this->isVersion302() || $this->isVersion4()) $this->skip();

		$expected = 'BOSS';
		$actual = (new TesseractOCR("{$this->imagesDir}/8055.png"))
			->executable($this->executable)
			->whitelist(range('A', 'Z'))
			->run();
		$this->assertEquals($expected, $actual);
	}

	public function testListAvailableLanguages()
	{
		// feature not available in this version of tesseract
		if ($this->isVersion302()) $this->skip();

		$actual = (new TesseractOCR())->availableLanguages();
		$this->assertEquals(true, in_array('deu', $actual));
		$this->assertEquals(true, in_array('eng', $actual));
		$this->assertEquals(true, in_array('jpn', $actual));
		$this->assertEquals(true, in_array('spa', $actual));
	}

	protected function isVersion302()
	{
		exec('tesseract --version 2>&1', $output);
		$version = explode(' ', $output[0])[1];
		return version_compare($version, '3.02', '>=')
			&& version_compare($version, '3.03', '<');
	}

	protected function isVersion4()
	{
		exec('tesseract --version 2>&1', $output);
		$version = explode(' ', $output[0])[1];
		return version_compare($version, '4.00', '>=');
	}
}
