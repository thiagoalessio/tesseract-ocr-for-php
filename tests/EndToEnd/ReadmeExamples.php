<?php namespace thiagoalessio\TesseractOCR\Tests\EndToEnd;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\TesseractOCR;

class ReadmeExamples extends TestCase
{
	private $executable = 'tesseract';
	private $imagesDir  = './tests/EndToEnd/images';
	private $fontsDir  = './tests/EndToEnd/fonts';

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

	public function testTemporaryFilesAreDeleted()
	{
		// https://github.com/thiagoalessio/tesseract-ocr-for-php/issues/169
		$ocr = new TesseractOCR("{$this->imagesDir}/text.png");
		$ocr->run();

		$this->assertEquals(false, file_exists($ocr->command->getOutputFile(false)));
		$this->assertEquals(false, file_exists($ocr->command->getOutputFile(true)));
	}

	public function testWithoutInputFile()
	{
		// Cannot read from stdin in version 3.02
		if ($this->isVersion302()) $this->skip();

		$expected = "The quick brown fox\njumps over\nthe lazy dog.";
		$actual = (new TesseractOCR)
			->imageData(file_get_contents("{$this->imagesDir}/text.png"), filesize("{$this->imagesDir}/text.png"))
			->executable($this->executable)
			->run();
		$this->assertEquals($expected, $actual);
	}

	public function testWithCraftedImage()
	{

		// Cannot read from stdin in version 3.02
		if ($this->isVersion302()) $this->skip();

		$expected = "The quick brown fox\njumps over\nthe lazy dog.";

		$image = imagecreatetruecolor(600, 1200);
		$textColor = imagecolorallocate($image, 200, 200, 50);
		imagettftext($image, 20, 0, 10, 40, $textColor, "{$this->fontsDir}/DejaVuSans.ttf", $expected);
		list($png, $size) = $this->getPNGImage($image);
		imagedestroy($image);

		$actual = (new TesseractOCR)
			->imageData($png, $size)
			->executable($this->executable)
			->run();
		$this->assertEquals($expected, $actual);
	}

	public function testWithoutOutputFile()
	{
		// Cannot write to stdout in version 3.02
		if ($this->isVersion302()) $this->skip();

		$expected = "The quick brown fox\njumps over\nthe lazy dog.";
		$actual = (new TesseractOCR("{$this->imagesDir}/text.png"))
			->executable($this->executable)
			->withoutTempFiles()
			->run();
		$this->assertEquals($expected, $actual);
	}

	public function testWithoutFiles()
	{
		// Cannot read from stdin and write to stdout in version 3.02
		if ($this->isVersion302()) $this->skip();

		$expected = "The quick brown fox\njumps over\nthe lazy dog.";
		$actual = (new TesseractOCR)
			->imageData(file_get_contents("{$this->imagesDir}/text.png"), filesize("{$this->imagesDir}/text.png"))
			->executable($this->executable)
			->withoutTempFiles()
			->run();
		$this->assertEquals($expected, $actual);
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

	protected function getPNGImage($img)
	{
		ob_start();
		imagepng($img, null, 0);
		$size = ob_get_length();
		$img = ob_get_clean();
		return [$img, $size];
	}
}
