<?php namespace thiagoalessio\TesseractOCR\Tests\Unit;

use thiagoalessio\TesseractOCR\Tests\Common\TestCase;
use thiagoalessio\TesseractOCR\Tests\Unit\TestableCommand;
use thiagoalessio\TesseractOCR\Command;
use thiagoalessio\TesseractOCR\Option;

class CommandTest extends TestCase
{
	public function setUp()
	{
		$this->customTempDir = __DIR__.DIRECTORY_SEPARATOR.'custom-temp-dir';
		mkdir($this->customTempDir);
	}

	public function tearDown()
	{
		$files = glob(join(DIRECTORY_SEPARATOR, array($this->customTempDir, '*')));
		array_map('unlink', $files);
		rmdir($this->customTempDir);
	}

	public function testSimplestCommand()
	{
		$cmd = new TestableCommand('image.png');

		$expected = '"tesseract" "image.png" "tmpfile"';
		$this->assertEquals("$expected", "$cmd");
	}

	public function testCommandWithOption()
	{
		$cmd = new TestableCommand('image.png');
		$cmd->options[] = Option::lang('eng');

		$expected = '"tesseract" "image.png" "tmpfile" -l eng';
		$this->assertEquals("$expected", "$cmd");
	}

	public function testWithConfigFile()
	{
		$cmd = new TestableCommand('image.png');
		$cmd->configFile = 'hocr';

		$expected = '"tesseract" "image.png" "tmpfile" hocr';
		$this->assertEquals("$expected", "$cmd");
	}

	public function testCustomTempDir()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') $this->skip();

		$cmd = new Command('image.png');
		$cmd->tempDir = $this->customTempDir;

		$expected = "\"tesseract\" \"image.png\" \"{$this->customTempDir}";
		$actual = substr("$cmd", 0, strlen($expected));
		$this->assertEquals("$expected", "$actual");
	}

	public function testCustomTempDirWindows()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') $this->skip();

		$customTempDir = 'C:\Users\Foo Bar\Temp\Dir';
		if (!file_exists($customTempDir)) mkdir($customTempDir, null, true);

		$cmd = new Command('image.png');
		$cmd->tempDir = $customTempDir;

		$expected = '"tesseract" "image.png" "C:\Users\Foo Bar\Temp\Dir';
		$actual = substr("$cmd", 0, strlen($expected));
		$this->assertEquals("$expected", "$actual");
	}

	public function testCommandWithThreadLimit()
	{
		$cmd = new TestableCommand('image.png');
		$cmd->threadLimit = 2;

		$expected = 'OMP_THREAD_LIMIT=2 "tesseract" "image.png" "tmpfile"';
		$this->assertEquals("$expected", "$cmd");
	}

	public function testEscapeSpecialCharactersOnFilename()
	{
		$cmd = new TestableCommand('$@ ! ? "#\'_`foo.png');

		$expected = '"tesseract" "\$@ ! ? \\"#\'_\`foo.png" "tmpfile"';
		$this->assertEquals("$expected", "$cmd");
	}
}
