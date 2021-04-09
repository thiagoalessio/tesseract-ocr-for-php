<?php namespace thiagoalessio\TesseractOCR\Tests\EndToEnd;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\TesseractOCR;
use thiagoalessio\TesseractOCR\Tests\Unit\TestableCommand;

use thiagoalessio\TesseractOCR\ImageNotFoundException;
use thiagoalessio\TesseractOCR\TesseractNotFoundException;
use thiagoalessio\TesseractOCR\UnsuccessfulCommandException;

class FriendlyErrors extends TestCase
{
	public function testImageNotFound()
	{
		$currentDir = realpath(
			join(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'src'])
		);

		$expected = array();
		$expected[] = 'Error! The image "/invalid/image.png" was not found.';
		$expected[] = '';
		$expected[] = "The current __DIR__ is $currentDir";
		$expected = join(PHP_EOL, $expected);

		try {
			(new TesseractOCR('/invalid/image.png'))->run();
			throw new \Exception('ImageNotFoundException not thrown');
		} catch (\Exception $e) {
			$this->assertEquals($expected, $e->getMessage());
		}
	}

	public function testExecutableNotFound()
	{
		$currentPath = getenv('PATH');

		$expected = array();
		$expected[] = 'Error! The command "/nowhere/tesseract" was not found.';
		$expected[] = '';
		$expected[] = 'Make sure you have Tesseract OCR installed on your system:';
		$expected[] = 'https://github.com/tesseract-ocr/tesseract';
		$expected[] = '';
		$expected[] = "The current \$PATH is $currentPath";
		$expected = join(PHP_EOL, $expected);

		try {
			(new TesseractOCR('./tests/EndToEnd/images/text.png'))
				->executable('/nowhere/tesseract')
				->run();
			throw new \Exception('TesseractNotFoundException not thrown');
		} catch (TesseractNotFoundException $e) {
			$this->assertEquals($expected, $e->getMessage());
		}
	}

	public function testExecutableNotFoundWithVersionCheckingOptions()
	{
		# Issue #210, reported by @samwilson

		$currentPath = getenv('PATH');

		$expected = array();
		$expected[] = 'Error! The command "/nowhere/tesseract" was not found.';
		$expected[] = '';
		$expected[] = 'Make sure you have Tesseract OCR installed on your system:';
		$expected[] = 'https://github.com/tesseract-ocr/tesseract';
		$expected[] = '';
		$expected[] = "The current \$PATH is $currentPath";
		$expected = join(PHP_EOL, $expected);

		try {
			(new TesseractOCR())
				->executable('/nowhere/tesseract')
				->imageData('irrelevant', 1234)
				->withoutTempFiles()
				->run();
			throw new \Exception('TesseractNotFoundException not thrown');
		} catch (TesseractNotFoundException $e) {
			$this->assertEquals($expected, $e->getMessage());
		}
	}

	public function testUnsuccessfulCommand()
	{
		$expected = array();
		$expected[] = 'Error! The command did not produce any output.';
		$expected[] = '';
		$expected[] = 'Generated command:';
		$expected[] = '"tesseract" "./tests/EndToEnd/images/not-an-image.txt" "tmpfile" quiet';
		$expected[] = '';
		$expected[] = 'Returned message:';

		switch (true) {

			case ($this->isVersion('3.02')):
				$expected[] = 'Error in pixReadStream: Unknown format: no pix returned';
				$expected[] = 'Error in pixRead: pix not read';
				$expected[] = 'Unsupported image type.';
				break;

			case ($this->isVersion('3.03')):
				$expected[] = 'Tesseract Open Source OCR Engine v3.03 with Leptonica';
				$expected[] = 'Error in pixReadStream: Unknown format: no pix returned';
				$expected[] = 'Error in pixRead: pix not read';
				$expected[] = 'Error in pixGetInputFormat: pix not defined';
				$expected[] = 'Error in fopenReadStream: file not found';
				$expected[] = 'Error in pixRead: image file not found: not an image';
				$expected[] = 'Error during processing.';
				break;

			case ($this->isVersion('3.04.01')):
				$expected[] = 'Tesseract Open Source OCR Engine v3.04.01 with Leptonica';
				$expected[] = 'Error in fopenReadStream: file not found';
				$expected[] = 'Error in pixRead: image file not found: not an image';
				$expected[] = 'Error during processing.';
				break;

			case ($this->isVersion('3.05.00dev')):
				$expected[] = 'Tesseract Open Source OCR Engine v3.05.00dev with Leptonica';
				$expected[] = 'read_params_file: Can\'t open quiet';
				$expected[] = 'Image file not an image cannot be read!';
				$expected[] = 'Error during processing.';
				break;

			default:
				$expected[] = 'Error in fopenReadStream: file not found';
				$expected[] = 'Error in pixRead: image file not found: not an image';
				$expected[] = 'Error during processing.';
		}
		$expected = join(PHP_EOL, $expected);

		$cmd = new TestableCommand();
		try {
			(new TesseractOCR('./tests/EndToEnd/images/not-an-image.txt', $cmd))
				->quiet()
				->run();
			throw new \Exception('UnsuccessfulCommandException not thrown');
		} catch (UnsuccessfulCommandException $e) {
			$this->assertEquals($expected, $e->getMessage());
		}
	}

	protected function isVersion($version)
	{
		exec('tesseract --version 2>&1', $output);
		return strpos($output[0], "tesseract $version") !== false;
	}
}
